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

use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Mutator\Mutator;
use Infection\TestFramework\TestFrameworkTypes;
use Symfony\Component\Finder\SplFileInfo;
use Webmozart\Assert\Assert;

/**
 * @internal
 * @final
 */
class Configuration
{
    private const LOG_VERBOSITY = [
        'all',
        'none',
        'default',
    ];

    private float $timeout;
    /** @var string[] */
    private array $sourceDirectories;
    /** @var iterable<SplFileInfo> */
    private iterable $sourceFiles;
    private string $sourceFilesFilter;
    /** @var string[] */
    private array $sourceFilesExcludes;
    private Logs $logs;
    private string $logVerbosity;
    private string $tmpDir;
    private PhpUnit $phpUnit;
    /** @var array<string, Mutator<\PhpParser\Node>> */
    private array $mutators;
    private string $testFramework;
    private ?string $bootstrap = null;
    private ?string $initialTestsPhpOptions = null;
    private string $testFrameworkExtraOptions;
    private string $coveragePath;
    private bool $skipCoverage;
    private bool $skipInitialTests;
    private bool $debug;
    private bool $onlyCovered;
    private bool $noProgress;
    private bool $ignoreMsiWithNoMutations;
    private ?float $minMsi = null;
    private bool $showMutations;
    private ?float $minCoveredMsi = null;
    private int $msiPrecision;
    private int $threadCount;
    private bool $dryRun;
    /** @var array<string, array<int, string>> */
    private array $ignoreSourceCodeMutatorsMap;
    private bool $executeOnlyCoveringTestCases;
    private bool $isForGitDiffLines;
    private ?string $gitDiffBase;

    /**
     * @param string[] $sourceDirectories
     * @param string[] $sourceFilesExcludes
     * @param iterable<SplFileInfo> $sourceFiles
     * @param array<string, Mutator<\PhpParser\Node>> $mutators
     * @param array<string, array<int, string>> $ignoreSourceCodeMutatorsMap
     */
    public function __construct(
        float $timeout,
        array $sourceDirectories,
        iterable $sourceFiles,
        string $sourceFilesFilter,
        array $sourceFilesExcludes,
        Logs $logs,
        string $logVerbosity,
        string $tmpDir,
        PhpUnit $phpUnit,
        array $mutators,
        string $testFramework,
        ?string $bootstrap,
        ?string $initialTestsPhpOptions,
        string $testFrameworkExtraOptions,
        string $coveragePath,
        bool $skipCoverage,
        bool $skipInitialTests,
        bool $debug,
        bool $onlyCovered,
        bool $noProgress,
        bool $ignoreMsiWithNoMutations,
        ?float $minMsi,
        bool $showMutations,
        ?float $minCoveredMsi,
        int $msiPrecision,
        int $threadCount,
        bool $dryRun,
        array $ignoreSourceCodeMutatorsMap,
        bool $executeOnlyCoveringTestCases,
        bool $isForGitDiffLines,
        ?string $gitDiffBase
    ) {
        Assert::nullOrGreaterThanEq($timeout, 0);
        Assert::allString($sourceDirectories);
        Assert::allIsInstanceOf($mutators, Mutator::class);
        Assert::oneOf($logVerbosity, self::LOG_VERBOSITY);
        Assert::nullOrOneOf($testFramework, TestFrameworkTypes::TYPES);
        Assert::nullOrGreaterThanEq($minMsi, 0.);
        Assert::greaterThanEq($threadCount, 0);

        $this->timeout = $timeout;
        $this->sourceDirectories = $sourceDirectories;
        $this->sourceFiles = $sourceFiles;
        $this->sourceFilesFilter = $sourceFilesFilter;
        $this->sourceFilesExcludes = $sourceFilesExcludes;
        $this->logs = $logs;
        $this->logVerbosity = $logVerbosity;
        $this->tmpDir = $tmpDir;
        $this->phpUnit = $phpUnit;
        $this->mutators = $mutators;
        $this->testFramework = $testFramework;
        $this->bootstrap = $bootstrap;
        $this->initialTestsPhpOptions = $initialTestsPhpOptions;
        $this->testFrameworkExtraOptions = $testFrameworkExtraOptions;
        $this->coveragePath = $coveragePath;
        $this->skipCoverage = $skipCoverage;
        $this->skipInitialTests = $skipInitialTests;
        $this->debug = $debug;
        $this->onlyCovered = $onlyCovered;
        $this->noProgress = $noProgress;
        $this->ignoreMsiWithNoMutations = $ignoreMsiWithNoMutations;
        $this->minMsi = $minMsi;
        $this->showMutations = $showMutations;
        $this->minCoveredMsi = $minCoveredMsi;
        $this->msiPrecision = $msiPrecision;
        $this->threadCount = $threadCount;
        $this->dryRun = $dryRun;
        $this->ignoreSourceCodeMutatorsMap = $ignoreSourceCodeMutatorsMap;
        $this->executeOnlyCoveringTestCases = $executeOnlyCoveringTestCases;
        $this->isForGitDiffLines = $isForGitDiffLines;
        $this->gitDiffBase = $gitDiffBase;
    }

    public function getProcessTimeout(): float
    {
        return $this->timeout;
    }

    /**
     * @return string[]
     */
    public function getSourceDirectories(): array
    {
        return $this->sourceDirectories;
    }

    /**
     * @return iterable<SplFileInfo>
     */
    public function getSourceFiles(): iterable
    {
        return $this->sourceFiles;
    }

    public function getSourceFilesFilter(): string
    {
        return $this->sourceFilesFilter;
    }

    /**
     * @return string[]
     */
    public function getSourceFilesExcludes(): array
    {
        return $this->sourceFilesExcludes;
    }

    public function getLogs(): Logs
    {
        return $this->logs;
    }

    public function getLogVerbosity(): string
    {
        return $this->logVerbosity;
    }

    public function getTmpDir(): string
    {
        return $this->tmpDir;
    }

    public function getPhpUnit(): PhpUnit
    {
        return $this->phpUnit;
    }

    /**
     * @return array<string, Mutator<\PhpParser\Node>>
     */
    public function getMutators(): array
    {
        return $this->mutators;
    }

    public function getTestFramework(): string
    {
        return $this->testFramework;
    }

    public function getBootstrap(): ?string
    {
        return $this->bootstrap;
    }

    public function getInitialTestsPhpOptions(): ?string
    {
        return $this->initialTestsPhpOptions;
    }

    public function getTestFrameworkExtraOptions(): string
    {
        return $this->testFrameworkExtraOptions;
    }

    public function getCoveragePath(): string
    {
        return $this->coveragePath;
    }

    public function shouldSkipCoverage(): bool
    {
        return $this->skipCoverage;
    }

    public function shouldSkipInitialTests(): bool
    {
        return $this->skipInitialTests;
    }

    public function isDebugEnabled(): bool
    {
        return $this->debug;
    }

    public function mutateOnlyCoveredCode(): bool
    {
        return $this->onlyCovered;
    }

    public function noProgress(): bool
    {
        return $this->noProgress;
    }

    public function ignoreMsiWithNoMutations(): bool
    {
        return $this->ignoreMsiWithNoMutations;
    }

    public function getMinMsi(): ?float
    {
        return $this->minMsi;
    }

    public function showMutations(): bool
    {
        return $this->showMutations;
    }

    public function getMinCoveredMsi(): ?float
    {
        return $this->minCoveredMsi;
    }

    public function getMsiPrecision(): int
    {
        return $this->msiPrecision;
    }

    public function getThreadCount(): int
    {
        return $this->threadCount;
    }

    public function isDryRun(): bool
    {
        return $this->dryRun;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function getIgnoreSourceCodeMutatorsMap(): array
    {
        return $this->ignoreSourceCodeMutatorsMap;
    }

    public function getExecuteOnlyCoveringTestCases(): bool
    {
        return $this->executeOnlyCoveringTestCases;
    }

    public function isForGitDiffLines(): bool
    {
        return $this->isForGitDiffLines;
    }

    public function getGitDiffBase(): ?string
    {
        return $this->gitDiffBase;
    }
}
