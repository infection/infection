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

namespace Infection\Configuration\Schema;

use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\PhpStan;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Entry\Source;
use Infection\StaticAnalysis\StaticAnalysisToolTypes;
use Infection\TestFramework\TestFrameworkTypes;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final readonly class SchemaConfiguration
{
    /**
     * @param array<string, mixed> $mutators
     * @param TestFrameworkTypes::*|null $testFramework
     * @param StaticAnalysisToolTypes::*|null $staticAnalysisTool
     */
    public function __construct(
        private string $file,
        private ?float $timeout,
        private Source $source,
        private Logs $logs,
        private ?string $tmpDir,
        private PhpUnit $phpUnit,
        private PhpStan $phpStan,
        private ?bool $ignoreMsiWithNoMutations,
        private ?float $minMsi,
        private ?float $minCoveredMsi,
        private array $mutators,
        private ?string $testFramework,
        private ?string $bootstrap,
        private ?string $initialTestsPhpOptions,
        private ?string $testFrameworkExtraOptions,
        private ?string $staticAnalysisToolOptions,
        private string|int|null $threads,
        private ?string $staticAnalysisTool,
    ) {
        Assert::nullOrGreaterThanEq($timeout, 0);
        Assert::nullOrOneOf($testFramework, TestFrameworkTypes::getTypes());
        Assert::nullOrOneOf($staticAnalysisTool, StaticAnalysisToolTypes::getTypes());
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getTimeout(): ?float
    {
        return $this->timeout;
    }

    public function getSource(): Source
    {
        return $this->source;
    }

    public function getLogs(): Logs
    {
        return $this->logs;
    }

    public function getTmpDir(): ?string
    {
        return $this->tmpDir;
    }

    public function getPhpUnit(): PhpUnit
    {
        return $this->phpUnit;
    }

    public function getPhpStan(): PhpStan
    {
        return $this->phpStan;
    }

    public function getIgnoreMsiWithNoMutations(): ?bool
    {
        return $this->ignoreMsiWithNoMutations;
    }

    public function getMinMsi(): ?float
    {
        return $this->minMsi;
    }

    public function getMinCoveredMsi(): ?float
    {
        return $this->minCoveredMsi;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMutators(): array
    {
        return $this->mutators;
    }

    /**
     * @return TestFrameworkTypes::*|null
     */
    public function getTestFramework(): ?string
    {
        return $this->testFramework;
    }

    /**
     * @return StaticAnalysisToolTypes::*|null
     */
    public function getStaticAnalysisTool(): ?string
    {
        return $this->staticAnalysisTool;
    }

    public function getBootstrap(): ?string
    {
        return $this->bootstrap;
    }

    public function getInitialTestsPhpOptions(): ?string
    {
        return $this->initialTestsPhpOptions;
    }

    public function getTestFrameworkExtraOptions(): ?string
    {
        return $this->testFrameworkExtraOptions;
    }

    public function getStaticAnalysisToolOptions(): ?string
    {
        return $this->staticAnalysisToolOptions;
    }

    public function getThreads(): string|int|null
    {
        return $this->threads;
    }
}
