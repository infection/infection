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

use function implode;
use Infection\Mutant\MetricsCalculator;
use Infection\Mutant\MutantExecutionResult;
use function Safe\sprintf;
use function str_repeat;
use function strlen;

/**
 * @internal
 */
final class TextFileLogger implements LineMutationTestingResultsLogger
{
    private $metricsCalculator;
    private $debugVerbosity;
    private $onlyCoveredMode;
    private $debugMode;

    public function __construct(
        MetricsCalculator $metricsCalculator,
        bool $debugVerbosity,
        bool $onlyCoveredMode,
        bool $debugMode
    ) {
        $this->metricsCalculator = $metricsCalculator;
        $this->debugVerbosity = $debugVerbosity;
        $this->onlyCoveredMode = $onlyCoveredMode;
        $this->debugMode = $debugMode;
    }

    public function getLogLines(): array
    {
        $logs[] = $this->getLogParts($this->metricsCalculator->getEscapedMutantExecutionResults(), 'Escaped');
        $logs[] = $this->getLogParts($this->metricsCalculator->getTimedOutMutantExecutionResults(), 'Timed Out');

        if ($this->debugVerbosity) {
            $logs[] = $this->getLogParts($this->metricsCalculator->getKilledMutantExecutionResults(), 'Killed');
            $logs[] = $this->getLogParts($this->metricsCalculator->getErrorMutantExecutionResults(), 'Errors');
        }

        if (!$this->onlyCoveredMode) {
            $logs[] = $this->getLogParts($this->metricsCalculator->getNotCoveredMutantExecutionResults(), 'Not Covered');
        }

        return $logs;
    }

    /**
     * @param MutantExecutionResult[] $executionResults
     */
    private function getLogParts(array $executionResults, string $headlinePrefix): string
    {
        $logParts = $this->getHeadlineParts($headlinePrefix);

        ProcessSorter::sortProcesses($executionResults);

        foreach ($executionResults as $index => $executionResult) {
            $isShowFullFormat = $this->debugVerbosity;

            $logParts[] = '';
            $logParts[] = $this->getMutatorFirstLine($index, $executionResult);

            $logParts[] = $this->debugMode ? $executionResult->getProcessCommandLine() : '';

            $logParts[] = $executionResult->getMutantDiff();

            if ($isShowFullFormat) {
                $logParts[] = $executionResult->getProcessOutput();
            }
        }

        return implode(PHP_EOL, $logParts);
    }

    /**
     * @return string[]
     */
    private function getHeadlineParts(string $headlinePrefix): array
    {
        $headline = sprintf('%s mutants:', $headlinePrefix);

        return [
            $headline,
            str_repeat('=', strlen($headline)),
            '',
        ];
    }

    private function getMutatorFirstLine(int $index, MutantExecutionResult $mutantProcess): string
    {
        return sprintf(
            '%d) %s:%d    [M] %s',
            $index + 1,
            $mutantProcess->getOriginalFilePath(),
            $mutantProcess->getOriginalStartingLine(),
            $mutantProcess->getMutatorName()
        );
    }
}
