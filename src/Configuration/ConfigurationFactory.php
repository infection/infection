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

namespace Infection\Configuration;

use function array_fill_keys;
use function array_key_exists;
use function array_unique;
use function array_values;
use function dirname;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Schema\SchemaConfiguration;
use Infection\FileSystem\SourceFileCollector;
use Infection\FileSystem\TmpDirProvider;
use Infection\Logger\GitHub\GitDiffFileProvider;
use Infection\Mutator\ConfigurableMutator;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorFactory;
use Infection\Mutator\MutatorParser;
use Infection\Mutator\MutatorResolver;
use Infection\TestFramework\TestFrameworkTypes;
use OndraM\CiDetector\CiDetector;
use OndraM\CiDetector\CiDetectorInterface;
use OndraM\CiDetector\Exception\CiNotDetectedException;
use function Safe\sprintf;
use Symfony\Component\Filesystem\Path;
use function sys_get_temp_dir;
use Webmozart\Assert\Assert;

/**
 * @internal
 * @final
 */
class ConfigurationFactory
{
    /**
     * Default allowed timeout (on a test basis) in seconds
     */
    private const DEFAULT_TIMEOUT = 10;

    private TmpDirProvider $tmpDirProvider;
    private MutatorResolver $mutatorResolver;
    private MutatorFactory $mutatorFactory;
    private MutatorParser $mutatorParser;
    private SourceFileCollector $sourceFileCollector;
    private CiDetectorInterface $ciDetector;
    private GitDiffFileProvider $gitDiffFileProvider;

    public function __construct(
        TmpDirProvider $tmpDirProvider,
        MutatorResolver $mutatorResolver,
        MutatorFactory $mutatorFactory,
        MutatorParser $mutatorParser,
        SourceFileCollector $sourceFileCollector,
        CiDetectorInterface $ciDetector,
        GitDiffFileProvider $gitDiffFileProvider
    ) {
        $this->tmpDirProvider = $tmpDirProvider;
        $this->mutatorResolver = $mutatorResolver;
        $this->mutatorFactory = $mutatorFactory;
        $this->mutatorParser = $mutatorParser;
        $this->sourceFileCollector = $sourceFileCollector;
        $this->ciDetector = $ciDetector;
        $this->gitDiffFileProvider = $gitDiffFileProvider;
    }

    public function create(
        SchemaConfiguration $schema,
        ?string $existingCoveragePath,
        ?string $initialTestsPhpOptions,
        bool $skipInitialTests,
        string $logVerbosity,
        bool $debug,
        bool $onlyCovered,
        bool $noProgress,
        ?bool $ignoreMsiWithNoMutations,
        ?float $minMsi,
        bool $showMutations,
        ?float $minCoveredMsi,
        int $msiPrecision,
        string $mutatorsInput,
        ?string $testFramework,
        ?string $testFrameworkExtraOptions,
        string $filter,
        int $threadCount,
        bool $dryRun,
        ?string $gitDiffFilter,
        bool $isForGitDiffLines,
        ?string $gitDiffBase,
        ?bool $useGitHubLogger,
        ?string $htmlLogFilePath,
        bool $useNoopMutators,
        bool $executeOnlyCoveringTestCases
    ): Configuration {
        $configDir = dirname($schema->getFile());

        $namespacedTmpDir = $this->retrieveTmpDir($schema, $configDir);

        $testFramework = $testFramework ?? $schema->getTestFramework() ?? TestFrameworkTypes::PHPUNIT;

        $skipCoverage = $existingCoveragePath !== null;

        $coverageBasePath = self::retrieveCoverageBasePath(
            $existingCoveragePath,
            $configDir,
            $namespacedTmpDir
        );

        $resolvedMutatorsArray = $this->resolveMutators($schema->getMutators(), $mutatorsInput);

        $mutators = $this->mutatorFactory->create($resolvedMutatorsArray, $useNoopMutators);
        $ignoreSourceCodeMutatorsMap = $this->retrieveIgnoreSourceCodeMutatorsMap($resolvedMutatorsArray);

        return new Configuration(
            $schema->getTimeout() ?? self::DEFAULT_TIMEOUT,
            $schema->getSource()->getDirectories(),
            $this->sourceFileCollector->collectFiles(
                $schema->getSource()->getDirectories(),
                $schema->getSource()->getExcludes()
            ),
            $this->retrieveFilter($filter, $gitDiffFilter, $isForGitDiffLines, $gitDiffBase, $schema->getSource()->getDirectories()),
            $schema->getSource()->getExcludes(),
            $this->retrieveLogs($schema->getLogs(), $useGitHubLogger, $htmlLogFilePath),
            $logVerbosity,
            $namespacedTmpDir,
            $this->retrievePhpUnit($schema, $configDir),
            $mutators,
            $testFramework,
            $schema->getBootstrap(),
            $initialTestsPhpOptions ?? $schema->getInitialTestsPhpOptions(),
            self::retrieveTestFrameworkExtraOptions($testFrameworkExtraOptions, $schema),
            $coverageBasePath,
            $skipCoverage,
            $skipInitialTests,
            $debug,
            $onlyCovered,
            $this->retrieveNoProgress($noProgress),
            self::retrieveIgnoreMsiWithNoMutations($ignoreMsiWithNoMutations, $schema),
            self::retrieveMinMsi($minMsi, $schema),
            $showMutations,
            self::retrieveMinCoveredMsi($minCoveredMsi, $schema),
            $msiPrecision,
            $threadCount,
            $dryRun,
            $ignoreSourceCodeMutatorsMap,
            $executeOnlyCoveringTestCases,
            $isForGitDiffLines,
            $gitDiffBase
        );
    }

    /**
     * @param array<string, mixed> $schemaMutators
     *
     * @return array<class-string<Mutator<\PhpParser\Node>&ConfigurableMutator<\PhpParser\Node>>, mixed[]>
     */
    private function resolveMutators(array $schemaMutators, string $mutatorsInput): array
    {
        if ($schemaMutators === []) {
            $schemaMutators = ['@default' => true];
        }

        $parsedMutatorsInput = $this->mutatorParser->parse($mutatorsInput);

        if ($parsedMutatorsInput === []) {
            $mutatorsList = $schemaMutators;
        } else {
            $mutatorsList = array_fill_keys($parsedMutatorsInput, true);
        }

        return $this->mutatorResolver->resolve($mutatorsList);
    }

    private function retrieveTmpDir(
        SchemaConfiguration $schema,
        string $configDir
    ): string {
        $tmpDir = (string) $schema->getTmpDir();

        if ($tmpDir === '') {
            $tmpDir = sys_get_temp_dir();
        } elseif (!Path::isAbsolute($tmpDir)) {
            $tmpDir = sprintf('%s/%s', $configDir, $tmpDir);
        }

        return $this->tmpDirProvider->providePath($tmpDir);
    }

    private function retrievePhpUnit(SchemaConfiguration $schema, string $configDir): PhpUnit
    {
        $phpUnit = clone $schema->getPhpUnit();

        $phpUnitConfigDir = $phpUnit->getConfigDir();

        if ($phpUnitConfigDir === null) {
            $phpUnit->setConfigDir($configDir);
        } elseif (!Path::isAbsolute($phpUnitConfigDir)) {
            $phpUnit->setConfigDir(sprintf(
                '%s/%s', $configDir, $phpUnitConfigDir
            ));
        }

        return $phpUnit;
    }

    private static function retrieveCoverageBasePath(
        ?string $existingCoveragePath,
        string $configDir,
        string $tmpDir
    ): string {
        if ($existingCoveragePath === null) {
            return $tmpDir;
        }

        if (Path::isAbsolute($existingCoveragePath)) {
            return $existingCoveragePath;
        }

        return sprintf('%s/%s', $configDir, $existingCoveragePath);
    }

    private static function retrieveTestFrameworkExtraOptions(
        ?string $testFrameworkExtraOptions,
        SchemaConfiguration $schema
    ): string {
        return $testFrameworkExtraOptions ?? $schema->getTestFrameworkExtraOptions() ?? '';
    }

    private function retrieveNoProgress(bool $noProgress): bool
    {
        return $noProgress || $this->ciDetector->isCiDetected();
    }

    private static function retrieveIgnoreMsiWithNoMutations(
        ?bool $ignoreMsiWithNoMutations,
        SchemaConfiguration $schema
    ): bool {
        return $ignoreMsiWithNoMutations ?? $schema->getIgnoreMsiWithNoMutations() ?? false;
    }

    private static function retrieveMinMsi(?float $minMsi, SchemaConfiguration $schema): ?float
    {
        return $minMsi ?? $schema->getMinMsi();
    }

    private static function retrieveMinCoveredMsi(?float $minCoveredMsi, SchemaConfiguration $schema): ?float
    {
        return $minCoveredMsi ?? $schema->getMinCoveredMsi();
    }

    /**
     * @param array<string, mixed[]> $resolvedMutatorsMap
     *
     * @return array<string, array<int, string>>
     */
    private function retrieveIgnoreSourceCodeMutatorsMap(array $resolvedMutatorsMap): array
    {
        $map = [];

        foreach ($resolvedMutatorsMap as $mutatorClassName => $config) {
            if (array_key_exists('ignoreSourceCodeByRegex', $config)) {
                $mutatorName = MutatorFactory::getMutatorNameForClassName($mutatorClassName);

                Assert::isArray($config['ignoreSourceCodeByRegex']);

                $map[$mutatorName] = array_values(array_unique($config['ignoreSourceCodeByRegex']));
            }
        }

        return $map;
    }

    /**
     * @param string[] $sourceDirectories
     */
    private function retrieveFilter(string $filter, ?string $gitDiffFilter, bool $isForGitDiffLines, ?string $gitDiffBase, array $sourceDirectories): string
    {
        if ($gitDiffFilter === null && !$isForGitDiffLines) {
            return $filter;
        }

        $baseBranch = $gitDiffBase ?? GitDiffFileProvider::DEFAULT_BASE;

        if ($isForGitDiffLines) {
            return $this->gitDiffFileProvider->provide('AM', $baseBranch, $sourceDirectories);
        }

        return $this->gitDiffFileProvider->provide($gitDiffFilter, $baseBranch, $sourceDirectories);
    }

    private function retrieveLogs(Logs $logs, ?bool $useGitHubLogger, ?string $htmlLogFilePath): Logs
    {
        if ($useGitHubLogger === null) {
            $useGitHubLogger = $this->detectCiGithubActions();
        }

        if ($useGitHubLogger) {
            $logs->setUseGitHubAnnotationsLogger($useGitHubLogger);
        }

        if ($htmlLogFilePath !== null) {
            $logs->setHtmlLogFilePath($htmlLogFilePath);
        }

        return $logs;
    }

    private function detectCiGithubActions(): bool
    {
        try {
            $ci = $this->ciDetector->detect();
        } catch (CiNotDetectedException $e) {
            return false;
        }

        return $ci->getCiName() === CiDetector::CI_GITHUB_ACTIONS;
    }
}
