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

namespace Infection\Tests\Configuration;

use function array_values;
use function implode;
use Infection\Configuration\Configuration;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\PhpStan;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Entry\Source;
use Infection\Configuration\Entry\StrykerConfig;
use Infection\Configuration\SourceFilter\PlainFilter;
use Infection\Configuration\SourceFilter\SourceFilter;
use Infection\Mutator\IgnoreConfig;
use Infection\Mutator\IgnoreMutator;
use Infection\Mutator\Mutator;
use Infection\StaticAnalysis\StaticAnalysisToolTypes;
use Infection\TestFramework\MapSourceClassToTestStrategy;
use Infection\TestFramework\TestFrameworkTypes;
use Infection\Tests\Fixtures\Mutator\FakeMutator;
use PhpParser\Node;

final class ConfigurationBuilder
{
    /**
     * @param array<string, Mutator<Node>> $mutators
     * @param array<string, array<int, string>> $ignoreSourceCodeMutatorsMap
     * @param non-empty-string $configPathname
     */
    private function __construct(
        private float $timeout,
        private Source $source,
        private ?SourceFilter $sourceFilter,
        private Logs $logs,
        private string $logVerbosity,
        private string $tmpDir,
        private PhpUnit $phpUnit,
        private PhpStan $phpStan,
        private array $mutators,
        private string $testFramework,
        private ?string $bootstrap,
        private ?string $initialTestsPhpOptions,
        private string $testFrameworkExtraOptions,
        private ?string $staticAnalysisToolOptions,
        private string $coveragePath,
        private bool $skipCoverage,
        private bool $skipInitialTests,
        private bool $debug,
        private bool $uncovered,
        private bool $noProgress,
        private bool $ignoreMsiWithNoMutations,
        private ?float $minMsi,
        private ?int $numberOfShownMutations,
        private ?float $minCoveredMsi,
        private bool $timeoutsAsEscaped,
        private ?int $maxTimeouts,
        private int $msiPrecision,
        private int $threadCount,
        private bool $dryRun,
        private array $ignoreSourceCodeMutatorsMap,
        private bool $executeOnlyCoveringTestCases,
        private ?string $mapSourceClassToTestStrategy,
        private ?string $loggerProjectRootDirectory,
        private ?string $staticAnalysisTool,
        private ?string $mutantId,
        private string $configPathname,
    ) {
    }

    public static function from(Configuration $configuration): self
    {
        return new self(
            timeout: $configuration->processTimeout,
            source: $configuration->source,
            sourceFilter: $configuration->sourceFilter,
            logs: $configuration->logs,
            logVerbosity: $configuration->logVerbosity,
            tmpDir: $configuration->tmpDir,
            phpUnit: $configuration->phpUnit,
            phpStan: $configuration->phpStan,
            mutators: $configuration->mutators,
            testFramework: $configuration->testFramework,
            bootstrap: $configuration->bootstrap,
            initialTestsPhpOptions: $configuration->initialTestsPhpOptions,
            testFrameworkExtraOptions: $configuration->testFrameworkExtraOptions,
            staticAnalysisToolOptions: $configuration->getStaticAnalysisToolOptions() === []
                ? null
                : implode(' ', $configuration->getStaticAnalysisToolOptions()),
            coveragePath: $configuration->coveragePath,
            skipCoverage: $configuration->skipCoverage,
            skipInitialTests: $configuration->skipInitialTests,
            debug: $configuration->isDebugEnabled,
            uncovered: !$configuration->mutateOnlyCoveredCode(),
            noProgress: $configuration->noProgress,
            ignoreMsiWithNoMutations: $configuration->ignoreMsiWithNoMutations,
            minMsi: $configuration->minMsi,
            numberOfShownMutations: $configuration->numberOfShownMutations,
            minCoveredMsi: $configuration->minCoveredMsi,
            timeoutsAsEscaped: $configuration->timeoutsAsEscaped,
            maxTimeouts: $configuration->maxTimeouts,
            msiPrecision: $configuration->msiPrecision,
            threadCount: $configuration->threadCount,
            dryRun: $configuration->isDryRun,
            ignoreSourceCodeMutatorsMap: $configuration->ignoreSourceCodeMutatorsMap,
            executeOnlyCoveringTestCases: $configuration->executeOnlyCoveringTestCases,
            mapSourceClassToTestStrategy: $configuration->mapSourceClassToTestStrategy,
            loggerProjectRootDirectory: $configuration->loggerProjectRootDirectory,
            staticAnalysisTool: $configuration->staticAnalysisTool,
            mutantId: $configuration->mutantId,
            configPathname: $configuration->configurationPathname,
        );
    }

    public static function withMinimalTestData(): self
    {
        return new self(
            timeout: 10.0,
            source: new Source(),
            sourceFilter: null,
            logs: Logs::createEmpty(),
            logVerbosity: 'none',
            tmpDir: '/tmp/infection',
            phpUnit: new PhpUnit(null, null),
            phpStan: new PhpStan(null, null),
            mutators: [],
            testFramework: TestFrameworkTypes::PHPUNIT,
            bootstrap: null,
            initialTestsPhpOptions: null,
            testFrameworkExtraOptions: '',
            staticAnalysisToolOptions: null,
            coveragePath: '',
            skipCoverage: false,
            skipInitialTests: false,
            debug: false,
            uncovered: false,
            noProgress: false,
            ignoreMsiWithNoMutations: false,
            minMsi: null,
            numberOfShownMutations: null,
            minCoveredMsi: null,
            timeoutsAsEscaped: false,
            maxTimeouts: null,
            msiPrecision: 2,
            threadCount: 1,
            dryRun: false,
            ignoreSourceCodeMutatorsMap: [],
            executeOnlyCoveringTestCases: false,
            mapSourceClassToTestStrategy: null,
            loggerProjectRootDirectory: null,
            staticAnalysisTool: null,
            mutantId: null,
            configPathname: '/path/to/project/infection.json5',
        );
    }

    public static function withCompleteTestData(): self
    {
        return new self(
            timeout: 5.0,
            source: new Source(
                ['src', 'lib'],
                ['vendor', 'tests'],
            ),
            sourceFilter: new PlainFilter([
                'src/Foo.php',
                'src/Bar.php',
            ]),
            logs: new Logs(
                textLogFilePath: 'text.log',
                htmlLogFilePath: 'report.html',
                summaryLogFilePath: 'summary.log',
                jsonLogFilePath: 'json.log',
                gitlabLogFilePath: 'gitlab.log',
                debugLogFilePath: 'debug.log',
                perMutatorFilePath: 'mutator.log',
                useGitHubAnnotationsLogger: true,
                strykerConfig: StrykerConfig::forBadge('master'),
                summaryJsonLogFilePath: 'summary.json',
            ),
            logVerbosity: 'default',
            tmpDir: '/tmp/infection-test',
            phpUnit: new PhpUnit('config/phpunit', 'bin/phpunit'),
            phpStan: new PhpStan('config/phpstan', 'bin/phpstan'),
            mutators: [
                'Fake' => new IgnoreMutator(
                    new IgnoreConfig([]),
                    new FakeMutator(),
                ),
            ],
            testFramework: TestFrameworkTypes::PHPUNIT,
            bootstrap: 'bootstrap.php',
            initialTestsPhpOptions: '-d memory_limit=1G',
            testFrameworkExtraOptions: '--verbose',
            staticAnalysisToolOptions: '--level=max',
            coveragePath: 'coverage',
            skipCoverage: true,
            skipInitialTests: true,
            debug: true,
            uncovered: true,
            noProgress: true,
            ignoreMsiWithNoMutations: true,
            minMsi: 50.0,
            numberOfShownMutations: 10,
            minCoveredMsi: 60.0,
            timeoutsAsEscaped: true,
            maxTimeouts: 5,
            msiPrecision: 2,
            threadCount: 4,
            dryRun: true,
            ignoreSourceCodeMutatorsMap: [
                'Foo\\Bar' => ['.*test.*'],
            ],
            executeOnlyCoveringTestCases: true,
            mapSourceClassToTestStrategy: MapSourceClassToTestStrategy::SIMPLE,
            loggerProjectRootDirectory: '/var/www/project',
            staticAnalysisTool: StaticAnalysisToolTypes::PHPSTAN,
            mutantId: 'abc123def456',
            configPathname: '/path/to/project/infection.json5',
        );
    }

    public function withTimeout(float $timeout): self
    {
        $clone = clone $this;
        $clone->timeout = $timeout;

        return $clone;
    }

    public function withSource(Source $source): self
    {
        $clone = clone $this;
        $clone->source = $source;

        return $clone;
    }

    /**
     * @param non-empty-string ...$sourceDirectories
     */
    public function withSourceDirectories(string ...$sourceDirectories): self
    {
        $clone = clone $this;
        $clone->source = new Source(
            array_values($sourceDirectories),
            $this->source->excludes,
        );

        return $clone;
    }

    /**
     * @param non-empty-string ...$sourceFilesExcludes
     */
    public function withSourceFilesExcludes(string ...$sourceFilesExcludes): self
    {
        $clone = clone $this;
        $clone->source = new Source(
            $this->source->directories,
            array_values($sourceFilesExcludes),
        );

        return $clone;
    }

    public function withSourceFilter(?SourceFilter $sourceFilter): self
    {
        $clone = clone $this;
        $clone->sourceFilter = $sourceFilter;

        return $clone;
    }

    public function withLogs(Logs $logs): self
    {
        $clone = clone $this;
        $clone->logs = $logs;

        return $clone;
    }

    public function withLogVerbosity(string $logVerbosity): self
    {
        $clone = clone $this;
        $clone->logVerbosity = $logVerbosity;

        return $clone;
    }

    public function withTmpDir(string $tmpDir): self
    {
        $clone = clone $this;
        $clone->tmpDir = $tmpDir;

        return $clone;
    }

    public function withPhpUnit(PhpUnit $phpUnit): self
    {
        $clone = clone $this;
        $clone->phpUnit = $phpUnit;

        return $clone;
    }

    public function withPhpStan(PhpStan $phpStan): self
    {
        $clone = clone $this;
        $clone->phpStan = $phpStan;

        return $clone;
    }

    /**
     * @param array<string, Mutator> $mutators
     */
    public function withMutators(array $mutators): self
    {
        $clone = clone $this;
        $clone->mutators = $mutators;

        return $clone;
    }

    public function withTestFramework(string $testFramework): self
    {
        $clone = clone $this;
        $clone->testFramework = $testFramework;

        return $clone;
    }

    public function withBootstrap(?string $bootstrap): self
    {
        $clone = clone $this;
        $clone->bootstrap = $bootstrap;

        return $clone;
    }

    public function withInitialTestsPhpOptions(?string $initialTestsPhpOptions): self
    {
        $clone = clone $this;
        $clone->initialTestsPhpOptions = $initialTestsPhpOptions;

        return $clone;
    }

    public function withTestFrameworkExtraOptions(string $testFrameworkExtraOptions): self
    {
        $clone = clone $this;
        $clone->testFrameworkExtraOptions = $testFrameworkExtraOptions;

        return $clone;
    }

    public function withStaticAnalysisToolOptions(?string $staticAnalysisToolOptions): self
    {
        $clone = clone $this;
        $clone->staticAnalysisToolOptions = $staticAnalysisToolOptions;

        return $clone;
    }

    public function withCoveragePath(string $coveragePath): self
    {
        $clone = clone $this;
        $clone->coveragePath = $coveragePath;

        return $clone;
    }

    public function withSkipCoverage(bool $skipCoverage): self
    {
        $clone = clone $this;
        $clone->skipCoverage = $skipCoverage;

        return $clone;
    }

    public function withSkipInitialTests(bool $skipInitialTests): self
    {
        $clone = clone $this;
        $clone->skipInitialTests = $skipInitialTests;

        return $clone;
    }

    public function withDebug(bool $debug): self
    {
        $clone = clone $this;
        $clone->debug = $debug;

        return $clone;
    }

    public function withUncovered(bool $uncovered): self
    {
        $clone = clone $this;
        $clone->uncovered = $uncovered;

        return $clone;
    }

    public function withNoProgress(bool $noProgress): self
    {
        $clone = clone $this;
        $clone->noProgress = $noProgress;

        return $clone;
    }

    public function withIgnoreMsiWithNoMutations(bool $ignoreMsiWithNoMutations): self
    {
        $clone = clone $this;
        $clone->ignoreMsiWithNoMutations = $ignoreMsiWithNoMutations;

        return $clone;
    }

    public function withMinMsi(?float $minMsi): self
    {
        $clone = clone $this;
        $clone->minMsi = $minMsi;

        return $clone;
    }

    public function withNumberOfShownMutations(?int $numberOfShownMutations): self
    {
        $clone = clone $this;
        $clone->numberOfShownMutations = $numberOfShownMutations;

        return $clone;
    }

    public function withMinCoveredMsi(?float $minCoveredMsi): self
    {
        $clone = clone $this;
        $clone->minCoveredMsi = $minCoveredMsi;

        return $clone;
    }

    public function withTimeoutsAsEscaped(bool $timeoutsAsEscaped): self
    {
        $clone = clone $this;
        $clone->timeoutsAsEscaped = $timeoutsAsEscaped;

        return $clone;
    }

    public function withMaxTimeouts(?int $maxTimeouts): self
    {
        $clone = clone $this;
        $clone->maxTimeouts = $maxTimeouts;

        return $clone;
    }

    public function withMsiPrecision(int $msiPrecision): self
    {
        $clone = clone $this;
        $clone->msiPrecision = $msiPrecision;

        return $clone;
    }

    public function withThreadCount(int $threadCount): self
    {
        $clone = clone $this;
        $clone->threadCount = $threadCount;

        return $clone;
    }

    public function withDryRun(bool $dryRun): self
    {
        $clone = clone $this;
        $clone->dryRun = $dryRun;

        return $clone;
    }

    /**
     * @param array<string, array<int, string>> $ignoreSourceCodeMutatorsMap
     */
    public function withIgnoreSourceCodeMutatorsMap(array $ignoreSourceCodeMutatorsMap): self
    {
        $clone = clone $this;
        $clone->ignoreSourceCodeMutatorsMap = $ignoreSourceCodeMutatorsMap;

        return $clone;
    }

    public function withExecuteOnlyCoveringTestCases(bool $executeOnlyCoveringTestCases): self
    {
        $clone = clone $this;
        $clone->executeOnlyCoveringTestCases = $executeOnlyCoveringTestCases;

        return $clone;
    }

    public function withMapSourceClassToTestStrategy(?string $mapSourceClassToTestStrategy): self
    {
        $clone = clone $this;
        $clone->mapSourceClassToTestStrategy = $mapSourceClassToTestStrategy;

        return $clone;
    }

    public function withLoggerProjectRootDirectory(?string $loggerProjectRootDirectory): self
    {
        $clone = clone $this;
        $clone->loggerProjectRootDirectory = $loggerProjectRootDirectory;

        return $clone;
    }

    public function withStaticAnalysisTool(?string $staticAnalysisTool): self
    {
        $clone = clone $this;
        $clone->staticAnalysisTool = $staticAnalysisTool;

        return $clone;
    }

    public function withMutantId(?string $mutantId): self
    {
        $clone = clone $this;
        $clone->mutantId = $mutantId;

        return $clone;
    }

    /**
     * @param non-empty-string $pathname
     */
    public function withConfigPathname(string $pathname): self
    {
        $clone = clone $this;
        $clone->configPathname = $pathname;

        return $clone;
    }

    public function build(): Configuration
    {
        return new Configuration(
            processTimeout: $this->timeout,
            source: $this->source,
            sourceFilter: $this->sourceFilter,
            logs: $this->logs,
            logVerbosity: $this->logVerbosity,
            tmpDir: $this->tmpDir,
            phpUnit: $this->phpUnit,
            phpStan: $this->phpStan,
            mutators: $this->mutators,
            testFramework: $this->testFramework,
            bootstrap: $this->bootstrap,
            initialTestsPhpOptions: $this->initialTestsPhpOptions,
            testFrameworkExtraOptions: $this->testFrameworkExtraOptions,
            staticAnalysisToolOptions: $this->staticAnalysisToolOptions,
            coveragePath: $this->coveragePath,
            skipCoverage: $this->skipCoverage,
            skipInitialTests: $this->skipInitialTests,
            isDebugEnabled: $this->debug,
            withUncovered: $this->uncovered,
            noProgress: $this->noProgress,
            ignoreMsiWithNoMutations: $this->ignoreMsiWithNoMutations,
            minMsi: $this->minMsi,
            numberOfShownMutations: $this->numberOfShownMutations,
            minCoveredMsi: $this->minCoveredMsi,
            timeoutsAsEscaped: $this->timeoutsAsEscaped,
            maxTimeouts: $this->maxTimeouts,
            msiPrecision: $this->msiPrecision,
            threadCount: $this->threadCount,
            isDryRun: $this->dryRun,
            ignoreSourceCodeMutatorsMap: $this->ignoreSourceCodeMutatorsMap,
            executeOnlyCoveringTestCases: $this->executeOnlyCoveringTestCases,
            mapSourceClassToTestStrategy: $this->mapSourceClassToTestStrategy,
            loggerProjectRootDirectory: $this->loggerProjectRootDirectory,
            staticAnalysisTool: $this->staticAnalysisTool,
            mutantId: $this->mutantId,
            configurationPathname: $this->configPathname,
        );
    }
}
