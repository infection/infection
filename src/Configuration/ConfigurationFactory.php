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
use function file_exists;
use function in_array;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\PhpStan;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Schema\SchemaConfiguration;
use Infection\Configuration\SourceFilter\GitDiffFilter;
use Infection\Configuration\SourceFilter\IncompleteGitDiffFilter;
use Infection\Configuration\SourceFilter\PlainFilter;
use Infection\Configuration\SourceFilter\SourceFilter;
use Infection\FileSystem\Locator\FileOrDirectoryNotFound;
use Infection\FileSystem\TmpDirProvider;
use Infection\Git\Git;
use Infection\Logger\FileLogger;
use Infection\Mutator\ConfigurableMutator;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorFactory;
use Infection\Mutator\MutatorParser;
use Infection\Mutator\MutatorResolver;
use Infection\Resource\Processor\CpuCoresCountProvider;
use Infection\Source\Exception\NoSourceFound;
use Infection\TestFramework\TestFrameworkTypes;
use function is_numeric;
use function max;
use OndraM\CiDetector\CiDetector;
use OndraM\CiDetector\CiDetectorInterface;
use OndraM\CiDetector\Exception\CiNotDetectedException;
use PhpParser\Node;
use function sprintf;
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

    public function __construct(
        private readonly TmpDirProvider $tmpDirProvider,
        private readonly MutatorResolver $mutatorResolver,
        private readonly MutatorFactory $mutatorFactory,
        private readonly MutatorParser $mutatorParser,
        private readonly CiDetectorInterface $ciDetector,
        private readonly Git $git,
    ) {
    }

    /**
     * @throws FileOrDirectoryNotFound
     * @throws NoSourceFound
     */
    public function create(
        SchemaConfiguration $schema,
        ?string $existingCoveragePath,
        ?string $initialTestsPhpOptions,
        bool $skipInitialTests,
        string $logVerbosity,
        bool $debug,
        bool $withUncovered,
        bool $noProgress,
        ?bool $ignoreMsiWithNoMutations,
        ?float $minMsi,
        ?int $numberOfShownMutations,
        ?float $minCoveredMsi,
        bool $timeoutsAsEscaped,
        ?int $maxTimeouts,
        int $msiPrecision,
        string $mutatorsInput,
        ?string $testFramework,
        ?string $testFrameworkExtraOptions,
        ?string $staticAnalysisToolOptions,
        PlainFilter|IncompleteGitDiffFilter|null $sourceFilter,
        ?int $threadCount,
        bool $dryRun,
        ?bool $useGitHubLogger,
        ?string $gitlabLogFilePath,
        ?string $htmlLogFilePath,
        ?string $textLogFilePath,
        ?string $summaryJsonLogFilePath,
        bool $useNoopMutators,
        bool $executeOnlyCoveringTestCases,
        ?string $mapSourceClassToTestStrategy,
        ?string $loggerProjectRootDirectory,
        ?string $staticAnalysisTool,
        ?string $mutantId,
    ): Configuration {
        $configDir = dirname($schema->pathname);

        $namespacedTmpDir = $this->retrieveTmpDir($schema, $configDir);

        $testFramework ??= $schema->testFramework ?? TestFrameworkTypes::PHPUNIT;
        $resultStaticAnalysisTool = $staticAnalysisTool ?? $schema->staticAnalysisTool;

        $skipCoverage = $existingCoveragePath !== null;

        $coverageBasePath = self::retrieveCoverageBasePath(
            $existingCoveragePath,
            $configDir,
            $namespacedTmpDir,
        );

        $this->includeUserBootstrap($schema->bootstrap);

        $resolvedMutatorsArray = $this->resolveMutators($schema->mutators, $mutatorsInput);

        $mutators = $this->mutatorFactory->create($resolvedMutatorsArray, $useNoopMutators);
        $ignoreSourceCodeMutatorsMap = $this->retrieveIgnoreSourceCodeMutatorsMap($resolvedMutatorsArray);

        $sourceFilter = $this->refineFilterIfNecessary($sourceFilter);

        return new Configuration(
            processTimeout: $schema->timeout ?? self::DEFAULT_TIMEOUT,
            source: $schema->source,
            sourceFilter: $sourceFilter,
            logs: $this->retrieveLogs($schema->logs, $configDir, $useGitHubLogger, $gitlabLogFilePath, $htmlLogFilePath, $textLogFilePath, $summaryJsonLogFilePath),
            logVerbosity: $logVerbosity,
            tmpDir: $namespacedTmpDir,
            phpUnit: $this->retrievePhpUnit($schema, $configDir),
            phpStan: $this->retrievePhpStan($schema, $configDir),
            mutators: $mutators,
            testFramework: $testFramework,
            bootstrap: $schema->bootstrap,
            initialTestsPhpOptions: $initialTestsPhpOptions ?? $schema->initialTestsPhpOptions,
            testFrameworkExtraOptions: self::retrieveTestFrameworkExtraOptions($testFrameworkExtraOptions, $schema),
            staticAnalysisToolOptions: self::retrieveStaticAnalysisToolOptions($staticAnalysisToolOptions, $schema),
            coveragePath: $coverageBasePath,
            skipCoverage: $skipCoverage,
            skipInitialTests: $skipInitialTests,
            isDebugEnabled: $debug,
            withUncovered: $withUncovered,
            noProgress: $this->retrieveNoProgress($noProgress),
            ignoreMsiWithNoMutations: self::retrieveIgnoreMsiWithNoMutations($ignoreMsiWithNoMutations, $schema),
            minMsi: self::retrieveMinMsi($minMsi, $schema),
            numberOfShownMutations: $numberOfShownMutations,
            minCoveredMsi: self::retrieveMinCoveredMsi($minCoveredMsi, $schema),
            timeoutsAsEscaped: self::retrieveTimeoutsAsEscaped($timeoutsAsEscaped, $schema),
            maxTimeouts: self::retrieveMaxTimeouts($maxTimeouts, $schema),
            msiPrecision: $msiPrecision,
            threadCount: $this->retrieveThreadCount($threadCount, $schema),
            isDryRun: $dryRun,
            ignoreSourceCodeMutatorsMap: $ignoreSourceCodeMutatorsMap,
            executeOnlyCoveringTestCases: $executeOnlyCoveringTestCases,
            mapSourceClassToTestStrategy: $mapSourceClassToTestStrategy,
            loggerProjectRootDirectory: $loggerProjectRootDirectory,
            staticAnalysisTool: $resultStaticAnalysisTool,
            mutantId: $mutantId,
            configurationPathname: $schema->pathname,
        );
    }

    /**
     * @throws FileOrDirectoryNotFound
     */
    private function includeUserBootstrap(?string $bootstrap): void
    {
        if ($bootstrap === null) {
            return;
        }

        if (!file_exists($bootstrap)) {
            throw FileOrDirectoryNotFound::fromFileName($bootstrap, [__DIR__]);
        }

        (static function (string $infectionBootstrapFile): void {
            require_once $infectionBootstrapFile;
        })($bootstrap);
    }

    /**
     * @param array<string, mixed> $schemaMutators
     *
     * @return array<class-string<Mutator<Node>&ConfigurableMutator<Node>>, mixed[]>
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

            if (array_key_exists('global-ignoreSourceCodeByRegex', $schemaMutators)) {
                $mutatorsList['global-ignoreSourceCodeByRegex'] = $schemaMutators['global-ignoreSourceCodeByRegex'];
            }
        }

        return $this->mutatorResolver->resolve($mutatorsList);
    }

    private function retrieveTmpDir(
        SchemaConfiguration $schema,
        string $configDir,
    ): string {
        $tmpDir = (string) $schema->tmpDir;

        if ($tmpDir === '') {
            $tmpDir = sys_get_temp_dir();
        } elseif (!Path::isAbsolute($tmpDir)) {
            $tmpDir = sprintf('%s/%s', $configDir, $tmpDir);
        }

        return $this->tmpDirProvider->providePath($tmpDir);
    }

    private function retrievePhpUnit(SchemaConfiguration $schema, string $configDir): PhpUnit
    {
        return $schema->phpUnit->withAbsolutePaths($configDir);
    }

    private function retrievePhpStan(SchemaConfiguration $schema, string $configDir): PhpStan
    {
        return $schema->phpStan->withAbsolutePaths($configDir);
    }

    private static function retrieveCoverageBasePath(
        ?string $existingCoveragePath,
        string $configDir,
        string $tmpDir,
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
        SchemaConfiguration $schema,
    ): string {
        return $testFrameworkExtraOptions ?? $schema->testFrameworkExtraOptions ?? '';
    }

    private static function retrieveStaticAnalysisToolOptions(
        ?string $staticAnalysisToolOptions,
        SchemaConfiguration $schema,
    ): ?string {
        return $staticAnalysisToolOptions ?? $schema->staticAnalysisToolOptions;
    }

    private function retrieveNoProgress(bool $noProgress): bool
    {
        return $noProgress || $this->ciDetector->isCiDetected();
    }

    private static function retrieveIgnoreMsiWithNoMutations(
        ?bool $ignoreMsiWithNoMutations,
        SchemaConfiguration $schema,
    ): bool {
        return $ignoreMsiWithNoMutations ?? $schema->ignoreMsiWithNoMutations ?? false;
    }

    private static function retrieveMinMsi(?float $minMsi, SchemaConfiguration $schema): ?float
    {
        return $minMsi ?? $schema->minMsi;
    }

    private static function retrieveMinCoveredMsi(?float $minCoveredMsi, SchemaConfiguration $schema): ?float
    {
        return $minCoveredMsi ?? $schema->minCoveredMsi;
    }

    private static function retrieveTimeoutsAsEscaped(bool $timeoutsAsEscaped, SchemaConfiguration $schema): bool
    {
        return $timeoutsAsEscaped || ($schema->timeoutsAsEscaped ?? false);
    }

    private static function retrieveMaxTimeouts(?int $maxTimeouts, SchemaConfiguration $schema): ?int
    {
        return $maxTimeouts ?? $schema->maxTimeouts;
    }

    /**
     * @param array<class-string, mixed[]> $resolvedMutatorsMap
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

    private function refineFilterIfNecessary(
        PlainFilter|IncompleteGitDiffFilter|null $sourceFilter,
    ): ?SourceFilter {
        if ($sourceFilter instanceof IncompleteGitDiffFilter) {
            return new GitDiffFilter(
                $sourceFilter->value,
                self::refineGitBase($sourceFilter->base),
            );
        }

        return $sourceFilter;
    }

    private function retrieveLogs(Logs $logs, string $configDir, ?bool $useGitHubLogger, ?string $gitlabLogFilePath, ?string $htmlLogFilePath, ?string $textLogFilePath, ?string $summaryJsonLogFilePath): Logs
    {
        if ($useGitHubLogger === null) {
            $useGitHubLogger = $this->detectCiGithubActions();
        }

        if ($useGitHubLogger) {
            $logs->setUseGitHubAnnotationsLogger($useGitHubLogger);
        }

        if ($gitlabLogFilePath !== null) {
            $logs->setGitlabLogFilePath($gitlabLogFilePath);
        }

        if ($htmlLogFilePath !== null) {
            $logs->setHtmlLogFilePath($htmlLogFilePath);
        }

        if ($textLogFilePath !== null) {
            $logs->setTextLogFilePath($textLogFilePath);
        }

        if ($summaryJsonLogFilePath !== null) {
            $logs->setSummaryJsonLogFilePath($summaryJsonLogFilePath);
        }

        return new Logs(
            self::pathToAbsolute($logs->getTextLogFilePath(), $configDir),
            self::pathToAbsolute($logs->getHtmlLogFilePath(), $configDir),
            self::pathToAbsolute($logs->getSummaryLogFilePath(), $configDir),
            self::pathToAbsolute($logs->getJsonLogFilePath(), $configDir),
            self::pathToAbsolute($logs->getGitlabLogFilePath(), $configDir),
            self::pathToAbsolute($logs->getDebugLogFilePath(), $configDir),
            self::pathToAbsolute($logs->getPerMutatorFilePath(), $configDir),
            $logs->getUseGitHubAnnotationsLogger(),
            $logs->getStrykerConfig(),
            self::pathToAbsolute($logs->getSummaryJsonLogFilePath(), $configDir),
        );
    }

    private function detectCiGithubActions(): bool
    {
        try {
            $ci = $this->ciDetector->detect();
        } catch (CiNotDetectedException) {
            return false;
        }

        return $ci->getCiName() === CiDetector::CI_GITHUB_ACTIONS;
    }

    private static function pathToAbsolute(
        ?string $path,
        string $configDir,
    ): ?string {
        if ($path === null) {
            return null;
        }

        if (in_array($path, FileLogger::ALLOWED_PHP_STREAMS, true)) {
            return $path;
        }

        if (Path::isAbsolute($path)) {
            return $path;
        }

        return sprintf('%s/%s', $configDir, $path);
    }

    private function retrieveThreadCount(?int $threadCount, SchemaConfiguration $schema): int
    {
        // user passed `--threads` option, already validated
        if ($threadCount !== null) {
            return $threadCount;
        }

        $threadsFromSchema = $schema->threads;

        if ($threadsFromSchema === null) {
            return 1;
        }

        // config has numeric string or integer value
        if (is_numeric($threadsFromSchema)) {
            return (int) $threadsFromSchema;
        }

        // config has `max` thread count
        Assert::same($threadsFromSchema, 'max', sprintf('The value of key `threads` in configuration file must be of type integer or string "max". String "%s" provided.', $threadsFromSchema));

        // we subtract 1 here to not use all the available cores by Infection
        return max(1, CpuCoresCountProvider::provide() - 1);
    }

    /**
     * @param non-empty-string|null $base
     *
     * @return non-empty-string
     */
    private function refineGitBase(?string $base): string
    {
        // When the user gives a base, we need to try to refine it.
        // For example, if the user created their feature branch:
        //
        //  main:     A --- B --- C
        //                         \
        //  feature:                D --- E  (user changes)
        //
        // Later, after others push to main
        //
        //  main:     A --- B --- C --- F --- G --- H
        //                         \
        //  feature:                D --- E  (user changes)
        //
        // Then `git diff main HEAD` will give (D,E,F,G,H). So infection would
        // touch code the user did not touch.
        //
        // To prevent this, we try to find the best common ancestor, here C.
        // As a result, we would do `git diff C HEAD` which would give (D,E).
        return $this->git->getBaseReference($base ?? $this->git->getDefaultBase());
    }
}
