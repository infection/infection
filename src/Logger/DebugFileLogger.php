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

use Infection\Metrics\MetricsCalculator;
use Infection\Mutation\MutationExecutionResult;
use function implode;
use function Safe\sprintf;
use function str_repeat;
use function strlen;
use const PHP_EOL;

/**
 * Simple loggers recording the mutation names and original first line. This is mostly intended for
 * internal purposes e.g. some end-to-end tests.
 *
 * @internal
 */
final class DebugFileLogger implements LineMutationTestingResultsLogger
{
    private $metricsCalculator;
    private $onlyCoveredMode;

    public function __construct(MetricsCalculator $metricsCalculator, bool $onlyCoveredMode)
    {
        $this->metricsCalculator = $metricsCalculator;
        $this->onlyCoveredMode = $onlyCoveredMode;
    }

    public function getLogLines(): array
    {
        $separateSections = false;

        $logs = [];

        $logs[] = 'Total: ' . $this->metricsCalculator->getTotalMutantsCount();
        $logs[] = '';
        $logs[] = $this->getResultsLine(
            $this->metricsCalculator->getKilledExecutionResults(),
            'Killed',
            $separateSections
        );
        $logs[] = $this->getResultsLine(
            $this->metricsCalculator->getErrorExecutionResults(),
            'Errors',
            $separateSections
        );
        $logs[] = $this->getResultsLine(
            $this->metricsCalculator->getEscapedExecutionResults(),
            'Escaped',
            $separateSections
        );
        $logs[] = $this->getResultsLine(
            $this->metricsCalculator->getTimedOutExecutionResults(),
            'Timed Out',
            $separateSections
        );

        if (!$this->onlyCoveredMode) {
            $logs[] = $this->getResultsLine(
                $this->metricsCalculator->getNotCoveredExecutionResults(),
                'Not Covered',
                $separateSections
            );
        }

        if ($separateSections) {
            $logs[] = '';
        }

        return $logs;
    }

    /**
     * @param MutationExecutionResult[] $executionResults
     */
    private function getResultsLine(
        array $executionResults,
        string $headlinePrefix,
        bool &$separateSections
    ): string {
        $lines = [];

        if ($separateSections) {
            $lines[] = '';
            $lines[] = '';
        }

        $lines[] = self::getHeadlineLines($headlinePrefix);

        $separateSections = false;

        foreach ($executionResults as $index => $executionResult) {
            if ($separateSections) {
                $lines[] = '';
            }

            $lines[] = 'Mutator: ' . $executionResult->getMutatorName();
            $lines[] = 'Line ' . $executionResult->getOriginalStartingLine();

            $separateSections = true;
        }

        return implode(PHP_EOL, $lines);
    }

    private static function getHeadlineLines(string $headlinePrefix): string
    {
        $headline = sprintf('%s mutants:', $headlinePrefix);

        return implode(
            PHP_EOL,
            [
                $headline,
                str_repeat('=', strlen($headline)),
                '',
            ]
        );
    }
}
