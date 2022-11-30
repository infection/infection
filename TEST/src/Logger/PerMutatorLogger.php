<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Logger;

use function array_fill;
use function array_unshift;
use function count;
use function implode;
use _HumbugBox9658796bb9f0\Infection\Metrics\MetricsCalculator;
use _HumbugBox9658796bb9f0\Infection\Metrics\ResultsCollector;
use function max;
use const PHP_ROUND_HALF_UP;
use function round;
use function _HumbugBox9658796bb9f0\Safe\ksort;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use function str_pad;
use const STR_PAD_LEFT;
use const STR_PAD_RIGHT;
use function str_repeat;
use function strlen;
final class PerMutatorLogger implements LineMutationTestingResultsLogger
{
    public function __construct(private MetricsCalculator $metricsCalculator, private ResultsCollector $resultsCollector)
    {
    }
    public function getLogLines() : array
    {
        $calculatorPerMutator = $this->createMetricsPerMutators();
        $table = [['Mutator', 'Mutations', 'Killed', 'Escaped', 'Errors', 'Syntax Errors', 'Timed Out', 'Skipped', 'Ignored', 'MSI (%s)', 'Covered MSI (%s)']];
        foreach ($calculatorPerMutator as $mutatorName => $calculator) {
            $table[] = [$mutatorName, (string) $calculator->getTotalMutantsCount(), (string) $calculator->getKilledCount(), (string) $calculator->getEscapedCount(), (string) $calculator->getErrorCount(), (string) $calculator->getSyntaxErrorCount(), (string) $calculator->getTimedOutCount(), (string) $calculator->getSkippedCount(), (string) $calculator->getIgnoredCount(), self::formatScore($calculator->getMutationScoreIndicator()), self::formatScore($calculator->getCoveredCodeMutationScoreIndicator())];
        }
        $logs = self::formatTable($table);
        $logs[] = '';
        array_unshift($logs, '# Effects per Mutator', '');
        return $logs;
    }
    private static function formatScore(float $score) : string
    {
        return sprintf('%0.2f', round($score, 2, PHP_ROUND_HALF_UP));
    }
    private static function formatTable(array $table) : array
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
    private static function calculateColumnSizes(array $table) : array
    {
        $sizes = array_fill(0, count($table[0]), 0);
        foreach ($table as $row) {
            foreach ($row as $columnNumber => $cell) {
                $sizes[$columnNumber] = max($sizes[$columnNumber], strlen($cell));
            }
        }
        return $sizes;
    }
    private static function padCell(string $value, int $size, bool $toLeft) : string
    {
        return str_pad($value, $size, ' ', $toLeft ? STR_PAD_LEFT : STR_PAD_RIGHT);
    }
    private static function createSeparatorRow(array $columnSizes) : string
    {
        $separatorRow = [];
        foreach ($columnSizes as $columnSize) {
            $separatorRow[] = str_repeat('-', $columnSize);
        }
        return '| ' . implode(' | ', $separatorRow) . ' |';
    }
    private function createMetricsPerMutators() : array
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
