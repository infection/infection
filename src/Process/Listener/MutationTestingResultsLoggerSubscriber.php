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

use Infection\Config\InfectionConfig;
use Infection\Configuration\Configuration;
use Infection\Console\LogVerbosity;
use Infection\EventDispatcher\EventSubscriberInterface;
use Infection\Events\MutationTestingFinished;
use Infection\Http\BadgeApiClient;
use Infection\Logger\BadgeLogger;
use Infection\Logger\DebugFileLogger;
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
final class MutationTestingResultsLoggerSubscriber implements EventSubscriberInterface
{
    /**
     * @var Configuration
     */
    private $infectionConfig;

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
     * @var OutputInterface
     */
    private $output;

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
        Configuration $infectionConfig,
        MetricsCalculator $metricsCalculator,
        Filesystem $fs,
        string $logVerbosity,
        bool $isDebugMode,
        bool $isOnlyCoveredMode = false
    ) {
        $this->output = $output;
        $this->infectionConfig = $infectionConfig;
        $this->metricsCalculator = $metricsCalculator;
        $this->fs = $fs;
        $this->logVerbosity = $logVerbosity;
        $this->isDebugMode = $isDebugMode;
        $this->isOnlyCoveredMode = $isOnlyCoveredMode;
    }

    public function getSubscribedEvents(): array
    {
        return [
            MutationTestingFinished::class => [$this, 'onMutationTestingFinished'],
        ];
    }

    public function onMutationTestingFinished(MutationTestingFinished $event): void
    {
        $logs = $this->infectionConfig->getLogs();

        $logTypes = [
            'badge' => null,
            'debug' => $logs->getDebugLogFilePath(),
            'perMutator' => $logs->getPerMutatorFilePath(),
            'summary' => $logs->getSummaryLogFilePath(),
            'text' => $logs->getTextLogFilePath(),
        ];

        if ([] === $logTypes) {
            return;
        }

        $logTypes = $this->filterLogTypes($logTypes);

        foreach ($logTypes as $logType => $config) {
            $this->useLogger($logType, $config);
        }
    }

    private function filterLogTypes(array $logTypes): array
    {
        foreach ($logTypes as $key => $value) {
            if ($this->logVerbosity === LogVerbosity::NONE) {
                if (!\in_array($key, ResultsLoggerTypes::ALLOWED_WITHOUT_LOGGING, true)) {
                    unset($logTypes[$key]);
                }

                continue;
            }

            if (!\in_array($key, ResultsLoggerTypes::ALL, true)) {
                unset($logTypes[$key]);
            }
        }

        return $logTypes;
    }

    private function useLogger(string $logType, $config): void
    {
        $isDebugVerbosity = $this->logVerbosity === LogVerbosity::DEBUG;

        switch ($logType) {
            case ResultsLoggerTypes::TEXT_FILE:
                (new TextFileLogger(
                    $this->output,
                    $config,
                    $this->metricsCalculator,
                    $this->fs,
                    $isDebugVerbosity,
                    $this->isDebugMode,
                    $this->isOnlyCoveredMode
                ))->log();

                break;
            case ResultsLoggerTypes::SUMMARY_FILE:
                (new SummaryFileLogger(
                    $this->output,
                    $config,
                    $this->metricsCalculator,
                    $this->fs,
                     $isDebugVerbosity,
                    $this->isDebugMode
                ))->log();

                break;
            case ResultsLoggerTypes::DEBUG_FILE:
                (new DebugFileLogger(
                    $this->output,
                    $config,
                    $this->metricsCalculator,
                    $this->fs,
                    $isDebugVerbosity,
                    $this->isDebugMode,
                    $this->isOnlyCoveredMode
                ))->log();

                break;
            case ResultsLoggerTypes::BADGE:
                (new BadgeLogger(
                    $this->output,
                    new BadgeApiClient($this->output),
                    $this->metricsCalculator,
                    $config
                ))->log();

                break;
            case ResultsLoggerTypes::PER_MUTATOR:
                (new PerMutatorLogger(
                    $this->output,
                    $config,
                    $this->metricsCalculator,
                    $this->fs,
                    $isDebugVerbosity,
                    $this->isDebugMode
                ))->log();

                break;
        }
    }
}
