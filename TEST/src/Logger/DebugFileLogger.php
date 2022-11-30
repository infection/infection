<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Logger;

use function implode;
use _HumbugBox9658796bb9f0\Infection\Metrics\MetricsCalculator;
use _HumbugBox9658796bb9f0\Infection\Metrics\ResultsCollector;
use _HumbugBox9658796bb9f0\Infection\Mutant\MutantExecutionResult;
use const PHP_EOL;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use function str_repeat;
use function strlen;
final class DebugFileLogger implements LineMutationTestingResultsLogger
{
    public function __construct(private MetricsCalculator $metricsCalculator, private ResultsCollector $resultsCollector, private bool $onlyCoveredMode)
    {
    }
    public function getLogLines() : array
    {
        $separateSections = \false;
        $logs = [];
        $logs[] = 'Total: ' . $this->metricsCalculator->getTotalMutantsCount();
        $logs[] = '';
        $logs[] = $this->getResultsLine($this->resultsCollector->getKilledExecutionResults(), 'Killed', $separateSections);
        $logs[] = $this->getResultsLine($this->resultsCollector->getErrorExecutionResults(), 'Errors', $separateSections);
        $logs[] = $this->getResultsLine($this->resultsCollector->getSyntaxErrorExecutionResults(), 'Syntax Errors', $separateSections);
        $logs[] = $this->getResultsLine($this->resultsCollector->getEscapedExecutionResults(), 'Escaped', $separateSections);
        $logs[] = $this->getResultsLine($this->resultsCollector->getTimedOutExecutionResults(), 'Timed Out', $separateSections);
        $logs[] = $this->getResultsLine($this->resultsCollector->getSkippedExecutionResults(), 'Skipped', $separateSections);
        $logs[] = $this->getResultsLine($this->resultsCollector->getIgnoredExecutionResults(), 'Ignored', $separateSections);
        if (!$this->onlyCoveredMode) {
            $logs[] = $this->getResultsLine($this->resultsCollector->getNotCoveredExecutionResults(), 'Not Covered', $separateSections);
        }
        if ($separateSections) {
            $logs[] = '';
        }
        return $logs;
    }
    private function getResultsLine(array $executionResults, string $headlinePrefix, bool &$separateSections) : string
    {
        $lines = [];
        if ($separateSections) {
            $lines[] = '';
            $lines[] = '';
        }
        $lines[] = self::getHeadlineLines($headlinePrefix);
        $separateSections = \false;
        foreach ($executionResults as $executionResult) {
            if ($separateSections) {
                $lines[] = '';
            }
            $lines[] = 'Mutator: ' . $executionResult->getMutatorName();
            $lines[] = 'Line ' . $executionResult->getOriginalStartingLine();
            $separateSections = \true;
        }
        return implode(PHP_EOL, $lines);
    }
    private static function getHeadlineLines(string $headlinePrefix) : string
    {
        $headline = sprintf('%s mutants:', $headlinePrefix);
        return implode(PHP_EOL, [$headline, str_repeat('=', strlen($headline)), '']);
    }
}
