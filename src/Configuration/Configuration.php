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
use Infection\Configuration\Entry\Mutator\Mutators;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Entry\Source;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class Configuration
{
    private const LOG_VERBOSITY = [
        'all',
        'none',
        'default',
    ];

    private const TEST_FRAMEWORKS = [
        'phpunit',
        'phpspec',
    ];

    private const FORMATTER = [
        'dot',
        'progress',
    ];

    private $timeout;
    private $source;
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
    private $stringMutators;

    public function __construct(
        ?int $timeout,
        Source $source,
        Logs $logs,
        string $logVerbosity,
        ?string $tmpDir,
        PhpUnit $phpUnit,
        Mutators $mutators,
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
        ?float $minCoveredMsi,
        ?string $stringMutators
    ) {
        Assert::nullOrGreaterThanEq($timeout, 1);
        Assert::oneOf($logVerbosity, self::LOG_VERBOSITY);
        Assert::nullOrOneOf($testFramework, self::TEST_FRAMEWORKS);
        Assert::oneOf($formatter, self::FORMATTER);
        Assert::nullOrGreaterThanEq($minMsi, 0.);

        $this->timeout = $timeout;
        $this->source = $source;
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
        $this->stringMutators = $stringMutators;
    }

    public function getTimeout(): ?int
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

    public function getLogVerbosity(): string
    {
        return $this->logVerbosity;
    }

    public function getTmpDir(): ?string
    {
        return $this->tmpDir;
    }

    public function getPhpUnit(): PhpUnit
    {
        return $this->phpUnit;
    }

    public function getMutators(): Mutators
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

    public function getStringMutators(): ?string
    {
        return $this->stringMutators;
    }
}
