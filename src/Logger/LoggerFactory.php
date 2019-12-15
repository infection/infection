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

namespace Infection\Logger;

use Infection\Console\LogVerbosity;
use Infection\Http\BadgeApiClient;
use Infection\Mutant\MetricsCalculator;
use stdClass;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class LoggerFactory
{
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
        MetricsCalculator $metricsCalculator,
        Filesystem $fs,
        string $logVerbosity,
        bool $isDebugMode,
        bool $isOnlyCoveredMode
    ) {
        $this->metricsCalculator = $metricsCalculator;
        $this->fs = $fs;
        $this->logVerbosity = $logVerbosity;
        $this->isDebugMode = $isDebugMode;
        $this->isOnlyCoveredMode = $isOnlyCoveredMode;
    }

    /**
     * @param string|stdClass $config
     */
    public function createLogger(OutputInterface $output, string $logType, $config): MutationTestingResultsLogger
    {
        if ($this->logVerbosity === LogVerbosity::NONE
            && !in_array($logType, ResultsLoggerTypes::ALLOWED_WITHOUT_LOGGING, true)
        ) {
            return new NullLogger();
        }

        $isDebugVerbosity = $this->logVerbosity === LogVerbosity::DEBUG;

        switch ($logType) {
            case ResultsLoggerTypes::TEXT_FILE:
                return new TextFileLogger(
                    $output,
                    $config,
                    $this->metricsCalculator,
                    $this->fs,
                    $isDebugVerbosity,
                    $this->isDebugMode,
                    $this->isOnlyCoveredMode
                );
            case ResultsLoggerTypes::SUMMARY_FILE:
                return new SummaryFileLogger(
                    $output,
                    $config,
                    $this->metricsCalculator,
                    $this->fs,
                    $isDebugVerbosity,
                    $this->isDebugMode
                );
            case ResultsLoggerTypes::DEBUG_FILE:
                return new DebugFileLogger(
                    $output,
                    $config,
                    $this->metricsCalculator,
                    $this->fs,
                    $isDebugVerbosity,
                    $this->isDebugMode,
                    $this->isOnlyCoveredMode
                );
            case ResultsLoggerTypes::BADGE:
                return new BadgeLogger(
                    $output,
                    new BadgeApiClient($output),
                    $this->metricsCalculator,
                    $config
                );
            case ResultsLoggerTypes::PER_MUTATOR:
                return new PerMutatorLogger(
                    $output,
                    $config,
                    $this->metricsCalculator,
                    $this->fs,
                    $isDebugVerbosity,
                    $this->isDebugMode
                );
            default:
                return new NullLogger();
        }
    }
}
