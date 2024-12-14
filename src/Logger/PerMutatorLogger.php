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

use function array_fill;
use function array_unshift;
use function count;
use function implode;
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\ResultsCollector;
use function ksort;
use function max;
use const PHP_ROUND_HALF_UP;
use function round;
use function sprintf;
use function str_pad;
use const STR_PAD_LEFT;
use const STR_PAD_RIGHT;
use function str_repeat;
use function strlen;

/**
 * @internal
 */
final readonly class PerMutatorLogger implements LineMutationTestingResultsLogger
{
    private const ROUND_PRECISION = 2;

    public function __construct(private MetricsCalculator $metricsCalculator, private ResultsCollector $resultsCollector)
    {
    }

    public function getLogLines(): array
    {
        $calculatorPerMutator = $this->createMetricsPerMutators();

        $table = [
            ['Mutator', 'Mutations', 'Killed', 'Escaped', 'Errors', 'Syntax Errors', 'Timed Out', 'Skipped', 'Ignored', 'MSI (%s)', 'Covered MSI (%s)'],
        ];

        foreach ($calculatorPerMutator as $mutatorName => $calculator) {
            /* @var string $mutatorName */
            /* @var MetricsCalculator $calculator */
            $table[] = [
                $mutatorName,
                (string) $calculator->getTotalMutantsCount(),
                (string) $calculator->getKilledCount(),
                (string) $calculator->getEscapedCount(),
                (string) $calculator->getErrorCount(),
                (string) $calculator->getSyntaxErrorCount(),
                (string) $calculator->getTimedOutCount(),
                (string) $calculator->getSkippedCount(),
                (string) $calculator->getIgnoredCount(),
                self::formatScore($calculator->getMutationScoreIndicator()),
                self::formatScore($calculator->getCoveredCodeMutationScoreIndicator()),
            ];
        }

        $logs = self::formatTable($table);
        $logs[] = '';

        array_unshift($logs, '# Effects per Mutator', '');

        return $logs;
    }

    private static function formatScore(float $score): string
    {
        return sprintf(
            '%0.2f',
            round($score, self::ROUND_PRECISION, PHP_ROUND_HALF_UP),
        );
    }

    /**
     * @param string[][] $table
     *
     * @return string[]
     */
    private static function formatTable(array $table): array
    {
        $columnSizes = self::calculateColumnSizes($table);

        $formattedTable = [];

        foreach ($table as $index => $row) {
            foreach ($columnSizes as $i => $columnSize) {
                $row[$i] = self::padCell($row[$i], $columnSize, $i !== 0);
            }

            $formattedTable[] = '| ' . implode(' | ', $row) . ' |';

            if ($index === 0) {
                $formattedTable[] = self::createSeparatorRow($columnSizes);
            }
        }

        return $formattedTable;
    }

    /**
     * @param string[][] $table
     *
     * @return int[]
     */
    private static function calculateColumnSizes(array $table): array
    {
        $sizes = array_fill(0, count($table[0]), 0);

        foreach ($table as $row) {
            foreach ($row as $columnNumber => $cell) {
                $sizes[$columnNumber] = max($sizes[$columnNumber], strlen($cell));
            }
        }

        return $sizes;
    }

    private static function padCell(string $value, int $size, bool $toLeft): string
    {
        return str_pad($value, $size, ' ', $toLeft ? STR_PAD_LEFT : STR_PAD_RIGHT);
    }

    /**
     * @param int[] $columnSizes
     */
    private static function createSeparatorRow(array $columnSizes): string
    {
        $separatorRow = [];

        foreach ($columnSizes as $columnSize) {
            $separatorRow[] = str_repeat('-', $columnSize);
        }

        return '| ' . implode(' | ', $separatorRow) . ' |';
    }

    /**
     * @return array<string, MetricsCalculator>
     */
    private function createMetricsPerMutators(): array
    {
        $allExecutionResults = $this->resultsCollector->getAllExecutionResults();

        $processPerMutator = [];

        foreach ($allExecutionResults as $executionResult) {
            $mutatorName = $executionResult->getMutatorName();
            $processPerMutator[$mutatorName][] = $executionResult;
        }

        $calculatorPerMutator = [];

        foreach ($processPerMutator as $mutator => $executionResults) {
            $calculator = new MetricsCalculator($this->metricsCalculator->getRoundingPrecision());
            $calculator->collect(...$executionResults);

            $calculatorPerMutator[$mutator] = $calculator;
        }

        ksort($calculatorPerMutator);

        return $calculatorPerMutator;
    }
}
