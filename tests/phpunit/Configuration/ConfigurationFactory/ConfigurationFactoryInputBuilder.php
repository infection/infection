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

namespace Infection\Tests\Configuration\ConfigurationFactory;

use Infection\Configuration\Schema\SchemaConfiguration;
use Infection\Configuration\SourceFilter\IncompleteGitDiffFilter;
use Infection\Configuration\SourceFilter\PlainFilter;

final class ConfigurationFactoryInputBuilder
{
    public function __construct(
        private ?string $existingCoveragePath,
        private ?string $initialTestsPhpOptions,
        private bool $skipInitialTests,
        private string $logVerbosity,
        private bool $debug,
        private bool $withUncovered,
        private bool $noProgress,
        private ?bool $ignoreMsiWithNoMutations,
        private ?float $minMsi,
        private ?int $numberOfShownMutations,
        private ?float $minCoveredMsi,
        private bool $timeoutsAsEscaped,
        private ?int $maxTimeouts,
        private int $msiPrecision,
        private string $mutatorsInput,
        private ?string $testFramework,
        private ?string $testFrameworkExtraOptions,
        private ?string $staticAnalysisToolOptions,
        private PlainFilter|IncompleteGitDiffFilter|null $sourceFilter,
        private ?int $threadCount,
        private bool $dryRun,
        private ?bool $useGitHubLogger,
        private ?string $gitlabLogFilePath,
        private ?string $htmlLogFilePath,
        private ?string $textLogFilePath,
        private ?string $summaryJsonLogFilePath,
        private bool $useNoopMutators,
        private bool $executeOnlyCoveringTestCases,
        private ?string $mapSourceClassToTestStrategy,
        private ?string $loggerProjectRootDirectory,
        private ?string $staticAnalysisTool,
        private ?string $mutantId,
    ) {
    }

    public function withExistingCoveragePath(?string $existingCoveragePath): self
    {
        $clone = clone $this;
        $clone->existingCoveragePath = $existingCoveragePath;

        return $clone;
    }

    public function withInitialTestsPhpOptions(?string $initialTestsPhpOptions): self
    {
        $clone = clone $this;
        $clone->initialTestsPhpOptions = $initialTestsPhpOptions;

        return $clone;
    }

    public function withSkipInitialTests(bool $skipInitialTests): self
    {
        $clone = clone $this;
        $clone->skipInitialTests = $skipInitialTests;

        return $clone;
    }

    public function withLogVerbosity(string $logVerbosity): self
    {
        $clone = clone $this;
        $clone->logVerbosity = $logVerbosity;

        return $clone;
    }

    public function withDebug(bool $debug): self
    {
        $clone = clone $this;
        $clone->debug = $debug;

        return $clone;
    }

    public function withWithUncovered(bool $withUncovered): self
    {
        $clone = clone $this;
        $clone->withUncovered = $withUncovered;

        return $clone;
    }

    public function withNoProgress(bool $noProgress): self
    {
        $clone = clone $this;
        $clone->noProgress = $noProgress;

        return $clone;
    }

    public function withIgnoreMsiWithNoMutations(?bool $ignoreMsiWithNoMutations): self
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

    public function withMutatorsInput(string $mutatorsInput): self
    {
        $clone = clone $this;
        $clone->mutatorsInput = $mutatorsInput;

        return $clone;
    }

    public function withTestFramework(?string $testFramework): self
    {
        $clone = clone $this;
        $clone->testFramework = $testFramework;

        return $clone;
    }

    public function withTestFrameworkExtraOptions(?string $testFrameworkExtraOptions): self
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

    public function withSourceFilter(PlainFilter|IncompleteGitDiffFilter|null $sourceFilter): self
    {
        $clone = clone $this;
        $clone->sourceFilter = $sourceFilter;

        return $clone;
    }

    public function withThreadCount(?int $threadCount): self
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

    public function withUseGitHubLogger(?bool $useGitHubLogger): self
    {
        $clone = clone $this;
        $clone->useGitHubLogger = $useGitHubLogger;

        return $clone;
    }

    public function withGitlabLogFilePath(?string $gitlabLogFilePath): self
    {
        $clone = clone $this;
        $clone->gitlabLogFilePath = $gitlabLogFilePath;

        return $clone;
    }

    public function withHtmlLogFilePath(?string $htmlLogFilePath): self
    {
        $clone = clone $this;
        $clone->htmlLogFilePath = $htmlLogFilePath;

        return $clone;
    }

    public function withTextLogFilePath(?string $textLogFilePath): self
    {
        $clone = clone $this;
        $clone->textLogFilePath = $textLogFilePath;

        return $clone;
    }

    public function withSummaryJsonLogFilePath(?string $summaryJsonLogFilePath): self
    {
        $clone = clone $this;
        $clone->summaryJsonLogFilePath = $summaryJsonLogFilePath;

        return $clone;
    }

    public function withUseNoopMutators(bool $useNoopMutators): self
    {
        $clone = clone $this;
        $clone->useNoopMutators = $useNoopMutators;

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
     * @return array{
     *     0: SchemaConfiguration,
     *     1: string|null,
     *     2: string|null,
     *     3: bool,
     *     4: string,
     *     5: bool,
     *     6: bool,
     *     7: bool,
     *     8: bool|null,
     *     9: float|null,
     *     10: int|null,
     *     11: float|null,
     *     12: bool,
     *     13: int|null,
     *     14: int,
     *     15: string,
     *     16: string|null,
     *     17: string|null,
     *     18: string|null,
     *     19: PlainFilter|IncompleteGitDiffFilter|null,
     *     20: int|null,
     *     21: bool,
     *     22: bool|null,
     *     23: string|null,
     *     24: string|null,
     *     25: string|null,
     *     26: string|null,
     *     27: bool,
     *     28: bool,
     *     29: string|null,
     *     30: string|null,
     *     31: string|null,
     *     32: string|null
     * }
     */
    public function build(SchemaConfiguration $schema): array
    {
        return [
            $schema,
            $this->existingCoveragePath,
            $this->initialTestsPhpOptions,
            $this->skipInitialTests,
            $this->logVerbosity,
            $this->debug,
            $this->withUncovered,
            $this->noProgress,
            $this->ignoreMsiWithNoMutations,
            $this->minMsi,
            $this->numberOfShownMutations,
            $this->minCoveredMsi,
            $this->timeoutsAsEscaped,
            $this->maxTimeouts,
            $this->msiPrecision,
            $this->mutatorsInput,
            $this->testFramework,
            $this->testFrameworkExtraOptions,
            $this->staticAnalysisToolOptions,
            $this->sourceFilter,
            $this->threadCount,
            $this->dryRun,
            $this->useGitHubLogger,
            $this->gitlabLogFilePath,
            $this->htmlLogFilePath,
            $this->textLogFilePath,
            $this->summaryJsonLogFilePath,
            $this->useNoopMutators,
            $this->executeOnlyCoveringTestCases,
            $this->mapSourceClassToTestStrategy,
            $this->loggerProjectRootDirectory,
            $this->staticAnalysisTool,
            $this->mutantId,
        ];
    }
}
