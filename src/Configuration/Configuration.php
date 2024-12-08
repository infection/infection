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
use PhpParser\Node;
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

    private readonly float $timeout;
    /** @var string[] */
    private readonly array $sourceDirectories;
    private readonly string $logVerbosity;
    /** @var array<string, Mutator<Node>> */
    private readonly array $mutators;
    private readonly string $testFramework;
    private ?float $minMsi = null;
    private readonly int $threadCount;

    /**
     * @param string[] $sourceDirectories
     * @param string[] $sourceFilesExcludes
     * @param iterable<SplFileInfo> $sourceFiles
     * @param array<string, Mutator<Node>> $mutators
     * @param array<string, array<int, string>> $ignoreSourceCodeMutatorsMap
     */
    public function __construct(
        float $timeout,
        array $sourceDirectories,
        private readonly iterable $sourceFiles,
        private readonly string $sourceFilesFilter,
        private readonly array $sourceFilesExcludes,
        private readonly Logs $logs,
        string $logVerbosity,
        private readonly string $tmpDir,
        private readonly PhpUnit $phpUnit,
        array $mutators,
        string $testFramework,
        private readonly ?string $bootstrap,
        private readonly ?string $initialTestsPhpOptions,
        private readonly string $testFrameworkExtraOptions,
        private readonly string $coveragePath,
        private readonly bool $skipCoverage,
        private readonly bool $skipInitialTests,
        private readonly bool $debug,
        private readonly bool $onlyCovered,
        private readonly bool $noProgress,
        private readonly bool $ignoreMsiWithNoMutations,
        ?float $minMsi,
        private readonly bool $showMutations,
        private readonly ?float $minCoveredMsi,
        private readonly int $msiPrecision,
        int $threadCount,
        private readonly bool $dryRun,
        private readonly array $ignoreSourceCodeMutatorsMap,
        private readonly bool $executeOnlyCoveringTestCases,
        private readonly bool $isForGitDiffLines,
        private readonly ?string $gitDiffBase,
        private readonly ?string $mapSourceClassToTestStrategy,
        private readonly ?string $loggerProjectRootDirectory,
    ) {
        Assert::nullOrGreaterThanEq($timeout, 0);
        Assert::allString($sourceDirectories);
        Assert::allIsInstanceOf($mutators, Mutator::class);
        Assert::oneOf($logVerbosity, self::LOG_VERBOSITY);
        Assert::nullOrOneOf($testFramework, TestFrameworkTypes::getTypes());
        Assert::nullOrGreaterThanEq($minMsi, 0.);
        Assert::greaterThanEq($threadCount, 0);

        $this->timeout = $timeout;
        $this->sourceDirectories = $sourceDirectories;
        $this->logVerbosity = $logVerbosity;
        $this->mutators = $mutators;
        $this->testFramework = $testFramework;
        $this->minMsi = $minMsi;
        $this->threadCount = $threadCount;
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
     * @return array<string, Mutator<Node>>
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

    public function getMapSourceClassToTestStrategy(): ?string
    {
        return $this->mapSourceClassToTestStrategy;
    }

    public function getLoggerProjectRootDirectory(): ?string
    {
        return $this->loggerProjectRootDirectory;
    }
}
