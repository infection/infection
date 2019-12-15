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

namespace Infection\Process\Listener;

use Infection\Console\LogVerbosity;
use Infection\Http\BadgeApiClient;
use Infection\Logger\BadgeLogger;
use Infection\Logger\DebugFileLogger;
use Infection\Logger\MutationTestingResultsLogger;
use Infection\Logger\PerMutatorLogger;
use Infection\Logger\ResultsLoggerTypes;
use Infection\Logger\SummaryFileLogger;
use Infection\Logger\TextFileLogger;
use Infection\Mutant\MetricsCalculator;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class LoggerFactory
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var MetricsCalculator
     */
    private $metricsCalculator;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var string
     */
    private $logVerbosity;

    /**
     * @var bool
     */
    private $isDebugMode;

    /**
     * @var bool
     */
    private $isOnlyCoveredMode;

    public function __construct(
        OutputInterface $output,
        MetricsCalculator $metricsCalculator,
        Filesystem $fs,
        string $logVerbosity,
        bool $isDebugMode,
        bool $isOnlyCoveredMode
    ) {
        $this->output = $output;
        $this->metricsCalculator = $metricsCalculator;
        $this->fs = $fs;
        $this->logVerbosity = $logVerbosity;
        $this->isDebugMode = $isDebugMode;
        $this->isOnlyCoveredMode = $isOnlyCoveredMode;
    }

    public function createConfig(string $logType, $config): MutationTestingResultsLogger
    {
        $isDebugVerbosity = $this->logVerbosity === LogVerbosity::DEBUG;

        switch ($logType) {
            case ResultsLoggerTypes::TEXT_FILE:
                return new TextFileLogger(
                    $this->output,
                    $config,
                    $this->metricsCalculator,
                    $this->fs,
                    $isDebugVerbosity,
                    $this->isDebugMode,
                    $this->isOnlyCoveredMode
                );
            case ResultsLoggerTypes::SUMMARY_FILE:
                return new SummaryFileLogger(
                    $this->output,
                    $config,
                    $this->metricsCalculator,
                    $this->fs,
                    $isDebugVerbosity,
                    $this->isDebugMode
                );
            case ResultsLoggerTypes::DEBUG_FILE:
                return new DebugFileLogger(
                    $this->output,
                    $config,
                    $this->metricsCalculator,
                    $this->fs,
                    $isDebugVerbosity,
                    $this->isDebugMode,
                    $this->isOnlyCoveredMode
                );
            case ResultsLoggerTypes::BADGE:
                return new BadgeLogger(
                    $this->output,
                    new BadgeApiClient($this->output),
                    $this->metricsCalculator,
                    $config
                );
            case ResultsLoggerTypes::PER_MUTATOR:
                return new PerMutatorLogger(
                    $this->output,
                    $config,
                    $this->metricsCalculator,
                    $this->fs,
                    $isDebugVerbosity,
                    $this->isDebugMode
                );
            default:
                throw new LogicException();
        }
    }
}
