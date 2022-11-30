<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Logger;

use function array_map;
use function explode;
use function implode;
use _HumbugBox9658796bb9f0\Infection\Metrics\ResultsCollector;
use _HumbugBox9658796bb9f0\Infection\Mutant\MutantExecutionResult;
use _HumbugBox9658796bb9f0\Infection\Str;
use const PHP_EOL;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use function str_repeat;
use function strlen;
final class TextFileLogger implements LineMutationTestingResultsLogger
{
    public function __construct(private ResultsCollector $resultsCollector, private bool $debugVerbosity, private bool $onlyCoveredMode, private bool $debugMode)
    {
    }
    public function getLogLines() : array
    {
        $separateSections = \false;
        $logs = [];
        $logs[] = $this->getResultsLine($this->resultsCollector->getEscapedExecutionResults(), 'Escaped', $separateSections);
        $logs[] = $this->getResultsLine($this->resultsCollector->getTimedOutExecutionResults(), 'Timed Out', $separateSections);
        $logs[] = $this->getResultsLine($this->resultsCollector->getSkippedExecutionResults(), 'Skipped', $separateSections);
        if ($this->debugVerbosity) {
            $logs[] = $this->getResultsLine($this->resultsCollector->getKilledExecutionResults(), 'Killed', $separateSections);
            $logs[] = $this->getResultsLine($this->resultsCollector->getErrorExecutionResults(), 'Errors', $separateSections);
            $logs[] = $this->getResultsLine($this->resultsCollector->getSyntaxErrorExecutionResults(), 'Syntax Errors', $separateSections);
        }
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
        foreach ($executionResults as $index => $executionResult) {
            if ($separateSections) {
                $lines[] = '';
                $lines[] = '';
            }
            $lines[] = self::getMutatorLine($index, $executionResult);
            $lines[] = '';
            $lines[] = Str::trimLineReturns($executionResult->getMutantDiff());
            if ($this->debugMode) {
                $lines[] = '';
                $lines[] = '$ ' . $executionResult->getProcessCommandLine();
            }
            if ($this->debugVerbosity) {
                if (!$this->debugMode) {
                    $lines[] = '';
                }
                $lines[] = self::getProcessOutputLine($executionResult->getProcessOutput());
            }
            $separateSections = \true;
        }
        return implode(PHP_EOL, $lines);
    }
    private static function getHeadlineLines(string $headlinePrefix) : string
    {
        $headline = sprintf('%s mutants:', $headlinePrefix);
        return implode(PHP_EOL, [$headline, str_repeat('=', strlen($headline)), '']);
    }
    private static function getMutatorLine(int $index, MutantExecutionResult $mutantProcess) : string
    {
        return sprintf('%d) %s:%d    [M] %s', $index + 1, $mutantProcess->getOriginalFilePath(), $mutantProcess->getOriginalStartingLine(), $mutantProcess->getMutatorName());
    }
    private static function getProcessOutputLine(string $value) : string
    {
        return implode(PHP_EOL, array_map(static fn(string $line): string => '  ' . $line, explode(PHP_EOL, Str::trimLineReturns($value))));
    }
}
