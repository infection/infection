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

use function array_filter;
use const ARRAY_FILTER_USE_KEY;
use Infection\Configuration\Entry\Badge;
use Infection\Configuration\Entry\Logs;
use Infection\Console\LogVerbosity;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 * @final
 */
class LoggerFactory
{
    private $logVerbosity;
    private $badgeLoggerFactory;
    private $debugFileLoggerFactory;
    private $perMutatorLoggerFactory;
    private $summaryFileLoggerFactory;
    private $textLoggerFactory;

    public function __construct(
        string $logVerbosity,
        BadgeLoggerFactory $badgeLoggerFactory,
        DebugFileLoggerFactory $debugFileLoggerFactory,
        PerMutatorLoggerFactory $perMutatorLoggerFactory,
        SummaryFileLoggerFactory $summaryFileLoggerFactory,
        TextLoggerFactory $textLoggerFactory
    ) {
        $this->logVerbosity = $logVerbosity;
        $this->badgeLoggerFactory = $badgeLoggerFactory;
        $this->debugFileLoggerFactory = $debugFileLoggerFactory;
        $this->perMutatorLoggerFactory = $perMutatorLoggerFactory;
        $this->summaryFileLoggerFactory = $summaryFileLoggerFactory;
        $this->textLoggerFactory = $textLoggerFactory;
    }

    /**
     * @return MutationTestingResultsLogger[]
     */
    public function createFromLogEntries(Logs $logs, OutputInterface $output): array
    {
        /** @var array<string, MutationTestingResultsLogger> $loggers */
        $loggers = array_filter([
            ResultsLoggerTypes::BADGE => $this->createBadgeLogger($output, $logs->getBadge()),
            ResultsLoggerTypes::DEBUG_FILE => $this->createDebugLogger(
                $output,
                $logs->getDebugLogFilePath()
            ),
            ResultsLoggerTypes::PER_MUTATOR => $this->createPerMutatorLogger(
                $output,
                $logs->getPerMutatorFilePath()
            ),
            ResultsLoggerTypes::SUMMARY_FILE => $this->createSummaryLogger(
                $output,
                $logs->getSummaryLogFilePath()
            ),
            ResultsLoggerTypes::TEXT_FILE => $this->createTextLogger(
                $output,
                $logs->getTextLogFilePath()
            ),
        ]);

        return array_filter(
            $loggers,
            function (string $loggerType): bool {
                return $this->isAllowedToLog($loggerType);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    private function createBadgeLogger(OutputInterface $output, ?Badge $badge): ?BadgeLogger
    {
        if ($badge === null) {
            return $badge;
        }

        return $this->badgeLoggerFactory->create($output, $badge->getBranch());
    }

    private function createDebugLogger(
        OutputInterface $output,
        ?string $location
    ): ?DebugFileLogger {
        return $location === null
            ? null
            : $this->debugFileLoggerFactory->create($output, $location)
        ;
    }

    private function createTextLogger(
        OutputInterface $output,
        ?string $location
    ): ?TextFileLogger {
        return $location === null
            ? null
            : $this->textLoggerFactory->create($output, $location)
        ;
    }

    private function createSummaryLogger(
        OutputInterface $output,
        ?string $location
    ): ?SummaryFileLogger {
        return $location === null
            ? null
            : $this->summaryFileLoggerFactory->create($output, $location)
        ;
    }

    private function createPerMutatorLogger(
        OutputInterface $output,
        ?string $location
    ): ?PerMutatorLogger {
        return $location === null
            ? null
            : $this->perMutatorLoggerFactory->create($output, $location)
        ;
    }

    private function isAllowedToLog(string $logType): bool
    {
        return $this->logVerbosity !== LogVerbosity::NONE
            || in_array($logType, ResultsLoggerTypes::ALLOWED_WITHOUT_LOGGING, true)
        ;
    }
}
