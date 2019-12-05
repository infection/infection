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
use Infection\Mutator\Util\Mutator;
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

    private const FORMATTER = [
        'dot',
        'progress',
    ];

    private $timeout;
    private $sourceFiles;
    private $logs;
    private $logVerbosity;
    private $tmpDir;
    private $phpUnit;
    private $mutators;
    private $testFramework;
    private $bootstrap;
    private $initialTestsPhpOptions;
    private $testFrameworkOptions;
    private $existingCoveragePath;
    private $debug;
    private $onlyCovered;
    private $formatter;
    private $noProgress;
    private $ignoreMsiWithNoMutations;
    private $minMsi;
    private $showMutations;
    private $minCoveredMsi;
    private $sourceDirectories;

    /**
     * @param SplFileInfo[]          $sourceFiles
     * @param string[]               $sourceDirectories
     * @param array<string, Mutator> $mutators
     */
    public function __construct(
        int $timeout,
        array $sourceFiles,
        array $sourceDirectories,
        Logs $logs,
        string $logVerbosity,
        string $tmpDir,
        PhpUnit $phpUnit,
        array $mutators,
        ?string $testFramework,
        ?string $bootstrap,
        ?string $initialTestsPhpOptions,
        ?string $testFrameworkOptions,
        ?string $existingCoveragePath,
        bool $debug,
        bool $onlyCovered,
        string $formatter,
        bool $noProgress,
        bool $ignoreMsiWithNoMutations,
        ?float $minMsi,
        bool $showMutations,
        ?float $minCoveredMsi
    ) {
        Assert::nullOrGreaterThanEq($timeout, 1);
        Assert::allIsInstanceOf($sourceFiles, SplFileInfo::class);
        Assert::allString($sourceDirectories);
        Assert::allIsInstanceOf($mutators, Mutator::class);
        Assert::oneOf($logVerbosity, self::LOG_VERBOSITY);
        Assert::nullOrOneOf($testFramework, TestFrameworkTypes::TYPES);
        Assert::oneOf($formatter, self::FORMATTER);
        Assert::nullOrGreaterThanEq($minMsi, 0.);

        $this->timeout = $timeout;
        $this->sourceFiles = $sourceFiles;
        $this->sourceDirectories = $sourceDirectories;
        $this->logs = $logs;
        $this->logVerbosity = $logVerbosity;
        $this->tmpDir = $tmpDir;
        $this->phpUnit = $phpUnit;
        $this->mutators = $mutators;
        $this->testFramework = $testFramework;
        $this->bootstrap = $bootstrap;
        $this->initialTestsPhpOptions = $initialTestsPhpOptions;
        $this->testFrameworkOptions = $testFrameworkOptions;
        $this->existingCoveragePath = $existingCoveragePath;
        $this->debug = $debug;
        $this->onlyCovered = $onlyCovered;
        $this->formatter = $formatter;
        $this->noProgress = $noProgress;
        $this->ignoreMsiWithNoMutations = $ignoreMsiWithNoMutations;
        $this->minMsi = $minMsi;
        $this->showMutations = $showMutations;
        $this->minCoveredMsi = $minCoveredMsi;
    }

    public function getProcessTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @return SplFileInfo[]
     */
    public function getSourceFiles(): array
    {
        return $this->sourceFiles;
    }

    /**
     * @return string[]
     */
    public function getSourceDirectories(): array
    {
        return $this->sourceDirectories;
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
     * @return array<string, Mutator>
     */
    public function getMutators(): array
    {
        return $this->mutators;
    }

    public function getTestFramework(): ?string
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

    public function getTestFrameworkOptions(): ?string
    {
        return $this->testFrameworkOptions;
    }

    public function getExistingCoveragePath(): ?string
    {
        return $this->existingCoveragePath;
    }

    public function isDebugEnabled(): bool
    {
        return $this->debug;
    }

    public function mutateOnlyCoveredCode(): bool
    {
        return $this->onlyCovered;
    }

    public function getFormatter(): string
    {
        return $this->formatter;
    }

    public function showProgress(): bool
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
}
