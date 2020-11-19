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

namespace Infection\Metrics;

use Generator;
use Infection\Configuration\Entry\Logs;
use Infection\Console\LogVerbosity;
use Infection\Logger\TextFileLogger;
use Infection\Mutant\DetectionStatus;
use function iterator_to_array;
use function Safe\array_flip;

/**
 * @internal
 * @final
 */
class TargetDetectionStatusesProvider
{
    private Logs $logConfig;
    private string $logVerbosity;
    private bool $onlyCoveredMode;
    private bool $showMutations;

    public function __construct(
        Logs $logConfig,
        string $logVerbosity,
        bool $onlyCoveredMode,
        bool $showMutations
    ) {
        $this->logConfig = $logConfig;
        $this->logVerbosity = $logVerbosity;
        $this->onlyCoveredMode = $onlyCoveredMode;
        $this->showMutations = $showMutations;
    }

    /**
     * Implementation follows the logic in LoggerFactory, TextFileLogger, etc.
     *
     * @see TextFileLogger
     *
     * @return array<string, mixed>
     */
    public function get(): array
    {
        if ($this->logVerbosity === LogVerbosity::NONE) {
            return [];
        }

        $allDetectionStatuses = array_flip(DetectionStatus::ALL);

        // This one requires them all.
        if ($this->logConfig->getDebugLogFilePath() !== null) {
            return $allDetectionStatuses;
        }

        // Per mutator logger needs all mutation results to make a summary.
        if ($this->logConfig->getPerMutatorFilePath() !== null) {
            return $allDetectionStatuses;
        }

        return array_flip(iterator_to_array($this->findRequired(), false));
    }

    /**
     * TODO This has to be a responsibility of loggers.
     *
     * @return Generator<string>
     */
    private function findRequired(): Generator
    {
        if ($this->showMutations) {
            yield DetectionStatus::ESCAPED;
        }

        if ($this->logConfig->getUseGitHubAnnotationsLogger()) {
            yield DetectionStatus::ESCAPED;
        }

        if ($this->logConfig->getJsonLogFilePath() !== null) {
            yield DetectionStatus::KILLED;

            yield DetectionStatus::ESCAPED;

            yield DetectionStatus::ERROR;

            yield DetectionStatus::TIMED_OUT;

            if (!$this->onlyCoveredMode) {
                yield DetectionStatus::NOT_COVERED;
            }
        }

        if ($this->logConfig->getTextLogFilePath() !== null) {
            yield DetectionStatus::ESCAPED;

            yield DetectionStatus::TIMED_OUT;

            yield DetectionStatus::SKIPPED;

            if ($this->logVerbosity === LogVerbosity::DEBUG) {
                yield DetectionStatus::KILLED;

                yield DetectionStatus::ERROR;
            }

            if (!$this->onlyCoveredMode) {
                yield DetectionStatus::NOT_COVERED;
            }
        }
    }
}
