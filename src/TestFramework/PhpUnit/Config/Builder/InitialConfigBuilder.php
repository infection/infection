<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Config\Builder;

use function array_slice;
use Closure;
use DOMDocument;
use DOMElement;
use function explode;
use function implode;
use function in_array;
use Infection\TestFramework\PhpUnit\Adapter\PhpUnitAdapter;
use Infection\TestFramework\PhpUnit\CommandLine\ArgumentsAndOptionsBuilder;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationManipulator;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationVersionProvider;
use Infection\TestFramework\XML\SafeDOMXPath;
use function sprintf;
use function str_starts_with;
use Symfony\Component\Filesystem\Filesystem;
use function version_compare;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final readonly class InitialConfigBuilder
{
    private const int CONFIGURATION_ARGUMENT_COUNT = 2;

    private string $originalXmlConfigContent;

    /**
     * @param Closure(string): void $logSkippedTestImpactAnalysis
     * @param string[] $srcDirs
     * @param string[] $filteredSourceFilesToMutate
     */
    public function __construct(
        private string $tmpDir,
        string $originalXmlConfigContent,
        private XmlConfigurationManipulator $configManipulator,
        private XmlConfigurationVersionProvider $versionProvider,
        private Filesystem $filesystem,
        private array $srcDirs,
        private array $filteredSourceFilesToMutate,
        private string $projectDir,
        private Closure $logSkippedTestImpactAnalysis,
    ) {
        Assert::notEmpty(
            $originalXmlConfigContent,
            'The original XML config content cannot be an empty string',
        );
        $this->originalXmlConfigContent = $originalXmlConfigContent;
    }

    public function build(string $version, bool $collectCoverage = true): string
    {
        $path = $this->buildPath();

        $xPath = SafeDOMXPath::fromString(
            $this->originalXmlConfigContent,
            preserveWhiteSpace: false,
            formatOutput: true,
        );

        $this->configManipulator->validate($path, $xPath);

        $this->addCoverageNodes($version, $xPath);
        $this->addRandomTestsOrderAttributesIfNotSet($version, $xPath);
        $this->configManipulator->addFailOnAttributesIfNotSet($version, $xPath);
        $this->configManipulator->replaceWithAbsolutePaths($xPath);
        $this->configManipulator->setStopOnFailureOrDefect($version, $xPath);
        $this->configManipulator->deactivateColours($xPath);

        if ($collectCoverage && PhpUnitAdapter::supportsTestImpactAnalysis($version)) {
            // TODO: Preserve composer.lock discovery when this configuration is written
            // outside the project directory; PHPUnit searches its parent directories.
            // TODO: Check cache writability and fall back with a diagnostic when unavailable.
            $phpunit = $xPath->getElement('/phpunit');
            $phpunit->setAttribute('recordTestRunHistory', 'true');
            $phpunit->setAttribute('recordTestImpactData', 'true');
            $phpunit->setAttribute('deriveTestImpactDataFromCoverageTargets', 'false');
            // TODO: Preserve the project's configured cache to reuse plain PHPUnit recordings.
            // Blocker: doc/TIA-notes.md#project-configured-cache-is-not-reused
            // Keep Infection's recording separate from the project's, and outside the cleaned tmpDir.
            $phpunit->setAttribute('cacheDirectory', $this->projectDir . '/.infection/phpunit');
        } else {
            $this->configManipulator->deactivateResultCaching($version, $xPath);
        }
        $this->configManipulator->deactivateStderrRedirection($xPath);
        $this->configManipulator->removeExistingLoggers($xPath);
        $this->configManipulator->removeExistingPrinters($xPath);

        $this->filesystem->dumpFile(
            $path,
            $xPath->document->saveXML(),
        );

        return $path;
    }

    /**
     * @param list<string> $options
     *
     * @return list<string>
     */
    public function configureTestImpactAnalysis(array $options, string $version, bool $collectCoverage): array
    {
        if (!$collectCoverage) {
            // Infection's --coverage option supplies existing reports for this run.
            return $options;
        }

        // TODO: File a separate bug report for accepting --no-coverage without supplied
        // coverage reports. This predates TIA; move validation to extra-argument handling later.
        Assert::notInArray(
            '--no-coverage',
            $options,
            "The PHPUnit --no-coverage option requires existing coverage reports supplied through Infection's --coverage option.",
        );

        if (!PhpUnitAdapter::supportsTestImpactAnalysis($version)) {
            return $options;
        }

        // TODO: Open a separate bug issue for configuration overrides bypassing Infection's
        // generated initial XML. Fix configuration selection separately; reject overrides
        // for now so TIA can rely on the configuration Infection generated.
        foreach (array_slice($options, self::CONFIGURATION_ARGUMENT_COUNT) as $option) {
            Assert::notInArray(
                explode('=', $option)[0],
                ['--configuration', '-c', '--no-configuration'],
                "PHPUnit configuration overrides via test-framework extra arguments are not supported with TIA yet. Use Infection's phpUnit.configDir setting instead.",
            );
        }

        if (in_array('--do-not-record-test-impact-data', $options, true)
            || in_array('--do-not-record-test-run-history', $options, true)
        ) {
            return [
                ...ArgumentsAndOptionsBuilder::withoutTestImpactOptions($options),
                '--do-not-record-test-impact-data',
            ];
        }

        $deriveFromTargets = false;

        foreach ($options as $option) {
            if ($option === '--derive-test-impact-data-from-coverage-targets') {
                $deriveFromTargets = true;
            }

            if ($option === '--do-not-derive-test-impact-data-from-coverage-targets') {
                $deriveFromTargets = false;
            }
        }

        if ($deriveFromTargets && !$this->requiresCoverageMetadataForAllTests()) {
            ($this->logSkippedTestImpactAnalysis)(
                'Deriving dependencies from coverage targets requires requireCoverageMetadata="true" without disabling it for any test size.',
            );

            return [...ArgumentsAndOptionsBuilder::withoutTestImpactOptions($options), '--do-not-record-test-impact-data'];
        }

        if ($this->filteredSourceFilesToMutate === []) {
            return $options;
        }

        // Do not intersect the user's test selection or impact query with another query.
        // Positional arguments and separate option values conservatively suppress automatic
        // selection as well; PHPUnit's complete option grammar is not duplicated here.
        foreach (array_slice($options, self::CONFIGURATION_ARGUMENT_COUNT) as $option) {
            if (!str_starts_with($option, '-')) {
                return $options;
            }

            if (in_array(explode('=', $option)[0], [
                '--filter', '--exclude-filter', '--testsuite', '--exclude-testsuite',
                '--group', '--exclude-group', '--covers', '--uses', '--test-suffix',
                '--run-test-id', '--test-id-filter-file', '--test-files-file', '--requires-php-extension',
                '--impacted-by', '--impacted-by-file', '--only-impacted', '--explain-impacted',
            ], true)) {
                return $options;
            }
        }

        // TODO: PHPUnit's explicit paths replace change detection. Retain changed-test and
        // data-provider safeguards before releasing this integration: a changed existing
        // test can start covering a selected source file without appearing in its old map.
        $path = $this->tmpDir . '/phpunit-impact-sources.txt';
        $this->filesystem->dumpFile($path, implode("\n", $this->filteredSourceFilesToMutate) . "\n");

        return [...$options, '--impacted-by-file', $path];
    }

    private function requiresCoverageMetadataForAllTests(): bool
    {
        // build() validates the original XML before we inspect its root attributes.
        $document = new DOMDocument();
        $document->loadXML($this->originalXmlConfigContent);
        $phpunit = $document->documentElement;
        Assert::isInstanceOf($phpunit, DOMElement::class);

        if (!in_array($phpunit->getAttribute('requireCoverageMetadata'), ['true', '1'], true)) {
            return false;
        }

        foreach (['Small', 'Medium', 'Large'] as $size) {
            $attribute = 'requireCoverageMetadataOn' . $size . 'Tests';

            if ($phpunit->hasAttribute($attribute) && !in_array($phpunit->getAttribute($attribute), ['true', '1'], true)) {
                return false;
            }
        }

        return true;
    }

    private function buildPath(): string
    {
        return $this->tmpDir . '/phpunitConfiguration.initial.infection.xml';
    }

    private function addCoverageNodes(string $version, SafeDOMXPath $xPath): void
    {
        if (version_compare($version, '12.0', '>=')) {
            // For PHPUnit 12.0+, preserve the original coverage configuration as-is.
            // Otherwise, if the initial tests executed cover code that is outside
            // configured sources, PHPUnit will fail with a warning.
            // Historically, this was done for performance reasons, but since then
            // PHPUnit coverage was reworked and optimised, and there are no more
            // benefits to doing this.
            $this->configManipulator->addOrUpdateSourceIncludeNodes(
                xPath: $xPath,
                srcDirs: $this->srcDirs,
                filteredSourceFilesToMutate: [],
            );

            return;
        }

        if (version_compare($version, '10.1', '>=')) {
            $this->configManipulator->addOrUpdateSourceIncludeNodes($xPath, $this->srcDirs, $this->filteredSourceFilesToMutate);

            return;
        }

        if (version_compare($version, '10', '>=')) {
            $this->configManipulator->addOrUpdateCoverageIncludeNodes($xPath, $this->srcDirs, $this->filteredSourceFilesToMutate);

            return;
        }

        if (version_compare($version, '9.3', '<')) {
            $this->configManipulator->addOrUpdateLegacyCoverageWhitelistNodes($xPath, $this->srcDirs, $this->filteredSourceFilesToMutate);

            return;
        }

        // For versions between 9.3 and 10.0, fallback to version provider
        if (version_compare($this->versionProvider->provide($xPath), '9.3', '>=')) {
            $this->configManipulator->addOrUpdateCoverageIncludeNodes($xPath, $this->srcDirs, $this->filteredSourceFilesToMutate);

            return;
        }

        $this->configManipulator->addOrUpdateLegacyCoverageWhitelistNodes($xPath, $this->srcDirs, $this->filteredSourceFilesToMutate);
    }

    private function addRandomTestsOrderAttributesIfNotSet(string $version, SafeDOMXPath $xPath): void
    {
        if (PhpUnitAdapter::supportsExecutionOrderDefectsRandom($version)) {
            if ($this->addAttributeIfNotSet('executionOrder', 'defects,random', $xPath)) {
                $this->addAttributeIfNotSet('resolveDependencies', 'true', $xPath);
            }
        } elseif (version_compare($version, '7.2', '>=')) {
            if ($this->addAttributeIfNotSet('executionOrder', 'random', $xPath)) {
                $this->addAttributeIfNotSet('resolveDependencies', 'true', $xPath);
            }
        }
    }

    private function addAttributeIfNotSet(string $attribute, string $value, SafeDOMXPath $xPath): bool
    {
        $count = $xPath->queryCount(sprintf('/phpunit/@%s', $attribute));

        if ($count === 0) {
            $xPath
                ->getElement('/phpunit')
                ->setAttribute($attribute, $value)
            ;

            return true;
        }

        return false;
    }
}
