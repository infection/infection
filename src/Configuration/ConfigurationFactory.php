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
use function array_map;
use function array_unique;
use function array_values;
use function dirname;
use function file_exists;
use function in_array;
use Infection\Configuration\Entry\GitOptions;
use Infection\Configuration\Entry\GitSource;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\PhpStan;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Entry\Source;
use Infection\Configuration\Schema\SchemaConfiguration;
use Infection\FileSystem\Locator\FileOrDirectoryNotFound;
use Infection\FileSystem\SourceFileCollector;
use Infection\FileSystem\TmpDirProvider;
use Infection\Logger\FileLogger;
use Infection\Logger\GitHub\GitDiffFileProvider;
use Infection\Mutator\ConfigurableMutator;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorFactory;
use Infection\Mutator\MutatorParser;
use Infection\Mutator\MutatorResolver;
use Infection\Resource\Processor\CpuCoresCountProvider;
use Infection\TestFramework\TestFrameworkTypes;
use function is_numeric;
use function max;
use OndraM\CiDetector\CiDetector;
use OndraM\CiDetector\CiDetectorInterface;
use OndraM\CiDetector\Exception\CiNotDetectedException;
use PhpParser\Node;
use function sprintf;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\SplFileInfo;
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
        private readonly SourceFileCollector $sourceFileCollector,
        private readonly CiDetectorInterface $ciDetector,
        private readonly GitDiffFileProvider $gitDiffFileProvider,
    ) {
    }

    /**
     * @param non-empty-string|GitOptions|null $sourceFilter
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
        int $msiPrecision,
        string $mutatorsInput,
        ?string $testFramework,
        ?string $testFrameworkExtraOptions,
        ?string $staticAnalysisToolOptions,
        string|GitOptions|null $sourceFilter,
        ?int $threadCount,
        bool $dryRun,
        ?bool $useGitHubLogger,
        ?string $gitlabLogFilePath,
        ?string $htmlLogFilePath,
        ?string $textLogFilePath,
        bool $useNoopMutators,
        bool $executeOnlyCoveringTestCases,
        ?string $mapSourceClassToTestStrategy,
        ?string $loggerProjectRootDirectory,
        ?string $staticAnalysisTool,
        ?string $mutantId,
    ): Configuration {
        $configDir = dirname($schema->file);

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

        return new Configuration(
            $schema->timeout ?? self::DEFAULT_TIMEOUT,
            $schema->source->directories,
            $this->collectFiles($schema),
            $this->retrieveFilter(
                $sourceFilter,
                $schema->source->directories,
            ),
            $this->getGitSource($sourceFilter),
            $schema->source->excludes,
            $this->retrieveLogs($schema->logs, $configDir, $useGitHubLogger, $gitlabLogFilePath, $htmlLogFilePath, $textLogFilePath),
            $logVerbosity,
            $namespacedTmpDir,
            $this->retrievePhpUnit($schema, $configDir),
            $this->retrievePhpStan($schema, $configDir),
            $mutators,
            $testFramework,
            $schema->bootstrap,
            $initialTestsPhpOptions ?? $schema->initialTestsPhpOptions,
            self::retrieveTestFrameworkExtraOptions($testFrameworkExtraOptions, $schema),
            self::retrieveStaticAnalysisToolOptions($staticAnalysisToolOptions, $schema),
            $coverageBasePath,
            $skipCoverage,
            $skipInitialTests,
            $debug,
            $withUncovered,
            $this->retrieveNoProgress($noProgress),
            self::retrieveIgnoreMsiWithNoMutations($ignoreMsiWithNoMutations, $schema),
            self::retrieveMinMsi($minMsi, $schema),
            $numberOfShownMutations,
            self::retrieveMinCoveredMsi($minCoveredMsi, $schema),
            $msiPrecision,
            $this->retrieveThreadCount($threadCount, $schema),
            $dryRun,
            $ignoreSourceCodeMutatorsMap,
            $executeOnlyCoveringTestCases,
            $mapSourceClassToTestStrategy,
            $loggerProjectRootDirectory,
            $resultStaticAnalysisTool,
            $mutantId,
        );
    }

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

    /**
     * @return iterable<string, SplFileInfo>
     */
    private function collectFiles(SchemaConfiguration $schema): iterable
    {
        $source = $schema->source;
        $schemaDirname = dirname($schema->file);

        $mapToAbsolutePath = static fn (string $path) => Path::isAbsolute($path)
            ? $path
            : Path::join(
                $schemaDirname,
                $path,
            );

        return $this->sourceFileCollector->collectFiles(
            // We need to make the source file paths absolute, otherwise the
            // collector will collect the files relative to the current working
            // directory instead of relative to the location of the configuration
            // file.
            array_map(
                $mapToAbsolutePath(...),
                $source->directories,
            ),
            $source->excludes,
        );
    }

    /**
     * @param non-empty-string|GitOptions|null $sourceFilter
     * @param string[] $sourceDirectories
     */
    private function retrieveFilter(
        string|GitOptions|null $sourceFilter,
        array $sourceDirectories,
    ): ?string {
        return $sourceFilter instanceof GitOptions
            ? $this->retrieveGitFilter($sourceFilter, $sourceDirectories)
            : $sourceFilter;
    }

    /**
     * @param string[] $sourceDirectories
     *
     * @return non-empty-string|null
     */
    private function retrieveGitFilter(
        GitOptions $options,
        array $sourceDirectories,
    ): string {
        $baseBranch = $gitDiffBase ?? $this->gitDiffFileProvider->provideDefaultBase();
        $filter = $options->isForDiffLines
            ? 'AM'
            : $options->filter;

        return $this->gitDiffFileProvider->provide($filter ?? '', $baseBranch, $sourceDirectories);
    }

    private function retrieveLogs(Logs $logs, string $configDir, ?bool $useGitHubLogger, ?string $gitlabLogFilePath, ?string $htmlLogFilePath, ?string $textLogFilePath): Logs
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

    private function getGitSource(string|GitOptions|null $sourceFilter): ?GitSource
    {
        return $sourceFilter instanceof GitOptions
            ? new GitSource(
                $sourceFilter->baseBranch ?? $this->gitDiffFileProvider->provideDefaultBase(),
                $sourceFilter->isForDiffLines,
            )
            : null;
    }
}
