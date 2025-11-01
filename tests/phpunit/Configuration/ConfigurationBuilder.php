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

use function implode;
use Infection\Configuration\Configuration;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\PhpStan;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Entry\StrykerConfig;
use Infection\Mutator\IgnoreConfig;
use Infection\Mutator\IgnoreMutator;
use Infection\Mutator\Mutator;
use Infection\StaticAnalysis\StaticAnalysisToolTypes;
use Infection\TestFramework\MapSourceClassToTestStrategy;
use Infection\TestFramework\TestFrameworkTypes;
use Infection\Tests\Fixtures\Mutator\FakeMutator;
use PhpParser\Node;
use Symfony\Component\Finder\SplFileInfo;

final class ConfigurationBuilder
{
    /**
     * @param string[] $sourceDirectories
     * @param iterable<SplFileInfo> $sourceFiles
     * @param string[] $sourceFilesExcludes
     * @param array<string, Mutator<Node>> $mutators
     * @param array<string, array<int, string>> $ignoreSourceCodeMutatorsMap
     */
    private function __construct(
        private float $timeout,
        private array $sourceDirectories,
        private iterable $sourceFiles,
        private string $sourceFilesFilter,
        private array $sourceFilesExcludes,
        private Logs $logs,
        private string $logVerbosity,
        private string $tmpDir,
        private PhpUnit $phpUnit,
        private PhpStan $phpStan,
        private array   $mutators,
        private string  $testFramework,
        private ?string $bootstrap,
        private ?string $initialTestsPhpOptions,
        private string  $testFrameworkExtraOptions,
        private ?string $staticAnalysisToolOptions,
        private string  $coveragePath,
        private bool    $skipCoverage,
        private bool    $skipInitialTests,
        private bool    $debug,
        private bool    $uncovered,
        private bool    $noProgress,
        private bool    $ignoreMsiWithNoMutations,
        private ?float  $minMsi,
        private ?int    $numberOfShownMutations,
        private ?float  $minCoveredMsi,
        private int     $msiPrecision,
        private int     $threadCount,
        private bool    $dryRun,
        private array   $ignoreSourceCodeMutatorsMap,
        private bool    $executeOnlyCoveringTestCases,
        private bool $isForGitDiffLines,
        private ?string $gitDiffBase,
        private ?string $mapSourceClassToTestStrategy,
        private ?string $loggerProjectRootDirectory,
        private ?string $staticAnalysisTool,
        private ?string $mutantId,
    ) {
    }

    public static function from(Configuration $configuration): self
    {
        return new self(
            $configuration->getProcessTimeout(),
            $configuration->getSourceDirectories(),
            $configuration->getSourceFiles(),
            $configuration->getSourceFilesFilter(),
            $configuration->getSourceFilesExcludes(),
            $configuration->getLogs(),
            $configuration->getLogVerbosity(),
            $configuration->getTmpDir(),
            $configuration->getPhpUnit(),
            $configuration->getPhpStan(),
            $configuration->getMutators(),
            $configuration->getTestFramework(),
            $configuration->getBootstrap(),
            $configuration->getInitialTestsPhpOptions(),
            $configuration->getTestFrameworkExtraOptions(),
            $configuration->getStaticAnalysisToolOptions() === []
                ? null
                : implode(' ', $configuration->getStaticAnalysisToolOptions()),
            $configuration->getCoveragePath(),
            $configuration->shouldSkipCoverage(),
            $configuration->shouldSkipInitialTests(),
            $configuration->isDebugEnabled(),
            !$configuration->mutateOnlyCoveredCode(),
            $configuration->noProgress(),
            $configuration->ignoreMsiWithNoMutations(),
            $configuration->getMinMsi(),
            $configuration->getNumberOfShownMutations(),
            $configuration->getMinCoveredMsi(),
            $configuration->getMsiPrecision(),
            $configuration->getThreadCount(),
            $configuration->isDryRun(),
            $configuration->getIgnoreSourceCodeMutatorsMap(),
            $configuration->getExecuteOnlyCoveringTestCases(),
            $configuration->isForGitDiffLines(),
            $configuration->getGitDiffBase(),
            $configuration->getMapSourceClassToTestStrategy(),
            $configuration->getLoggerProjectRootDirectory(),
            $configuration->getStaticAnalysisTool(),
            $configuration->getMutantId(),
        );
    }

    public static function withMinimalTestData(): self
    {
        return new self(
            timeout: 10.0,
            sourceDirectories: [],
            sourceFiles: [],
            sourceFilesFilter: '',
            sourceFilesExcludes: [],
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
            msiPrecision: 2,
            threadCount: 1,
            dryRun: false,
            ignoreSourceCodeMutatorsMap: [],
            executeOnlyCoveringTestCases: false,
            isForGitDiffLines: false,
            gitDiffBase: null,
            mapSourceClassToTestStrategy: null,
            loggerProjectRootDirectory: null,
            staticAnalysisTool: null,
            mutantId: null,
        );
    }

    public static function withCompleteTestData(): self
    {
        return new self(
            timeout: 5.0,
            sourceDirectories: ['src', 'lib'],
            sourceFiles: [
                new SplFileInfo('src/Foo.php', 'src/Foo.php', 'src/Foo.php'),
                new SplFileInfo('src/Bar.php', 'src/Bar.php', 'src/Bar.php'),
            ],
            sourceFilesFilter: 'src/Foo.php,src/Bar.php',
            sourceFilesExcludes: ['vendor', 'tests'],
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
            msiPrecision: 2,
            threadCount: 4,
            dryRun: true,
            ignoreSourceCodeMutatorsMap: [
                'Foo\\Bar' => ['.*test.*'],
            ],
            executeOnlyCoveringTestCases: true,
            isForGitDiffLines: true,
            gitDiffBase: 'origin/master',
            mapSourceClassToTestStrategy: MapSourceClassToTestStrategy::SIMPLE,
            loggerProjectRootDirectory: '/var/www/project',
            staticAnalysisTool: StaticAnalysisToolTypes::PHPSTAN,
            mutantId: 'abc123def456',
        );
    }

    public function withTimeout(float $timeout): self
    {
        $clone = clone $this;
        $clone->timeout = $timeout;

        return $clone;
    }

    public function withSourceDirectories(string ...$sourceDirectories): self
    {
        $clone = clone $this;
        $clone->sourceDirectories = $sourceDirectories;

        return $clone;
    }

    /**
     * @param iterable<SplFileInfo> $sourceFiles
     */
    public function withSourceFiles(iterable $sourceFiles): self
    {
        $clone = clone $this;
        $clone->sourceFiles = $sourceFiles;

        return $clone;
    }

    public function withSourceFilesFilter(string $sourceFilesFilter): self
    {
        $clone = clone $this;
        $clone->sourceFilesFilter = $sourceFilesFilter;

        return $clone;
    }

    public function withSourceFilesExcludes(string ...$sourceFilesExcludes): self
    {
        $clone = clone $this;
        $clone->sourceFilesExcludes = $sourceFilesExcludes;

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
     * @param array<string, Mutator<Node>> $mutators
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

    public function withIsForGitDiffLines(bool $isForGitDiffLines): self
    {
        $clone = clone $this;
        $clone->isForGitDiffLines = $isForGitDiffLines;

        return $clone;
    }

    public function withGitDiffBase(?string $gitDiffBase): self
    {
        $clone = clone $this;
        $clone->gitDiffBase = $gitDiffBase;

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

    public function build(): Configuration
    {
        return new Configuration(
            $this->timeout,
            $this->sourceDirectories,
            $this->sourceFiles,
            $this->sourceFilesFilter,
            $this->sourceFilesExcludes,
            $this->logs,
            $this->logVerbosity,
            $this->tmpDir,
            $this->phpUnit,
            $this->phpStan,
            $this->mutators,
            $this->testFramework,
            $this->bootstrap,
            $this->initialTestsPhpOptions,
            $this->testFrameworkExtraOptions,
            $this->staticAnalysisToolOptions,
            $this->coveragePath,
            $this->skipCoverage,
            $this->skipInitialTests,
            $this->debug,
            $this->uncovered,
            $this->noProgress,
            $this->ignoreMsiWithNoMutations,
            $this->minMsi,
            $this->numberOfShownMutations,
            $this->minCoveredMsi,
            $this->msiPrecision,
            $this->threadCount,
            $this->dryRun,
            $this->ignoreSourceCodeMutatorsMap,
            $this->executeOnlyCoveringTestCases,
            $this->isForGitDiffLines,
            $this->gitDiffBase,
            $this->mapSourceClassToTestStrategy,
            $this->loggerProjectRootDirectory,
            $this->staticAnalysisTool,
            $this->mutantId,
        );
    }
}
