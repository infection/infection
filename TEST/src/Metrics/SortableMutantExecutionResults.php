<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Metrics;

use _HumbugBox9658796bb9f0\Infection\Mutant\MutantExecutionResult;
use function _HumbugBox9658796bb9f0\Safe\usort;
final class SortableMutantExecutionResults
{
    private array $executionResults = [];
    private bool $sorted = \false;
    public function add(MutantExecutionResult $executionResult) : void
    {
        $this->executionResults[] = $executionResult;
        $this->sorted = \false;
    }
    public function getSortedExecutionResults() : array
    {
        if (!$this->sorted) {
            self::sortResults($this->executionResults);
            $this->sorted = \true;
        }
        return $this->executionResults;
    }
    private static function sortResults(array &$executionResults) : void
    {
        usort($executionResults, static function (MutantExecutionResult $a, MutantExecutionResult $b) : int {
            if ($a->getOriginalFilePath() === $b->getOriginalFilePath()) {
                return $a->getOriginalStartingLine() <=> $b->getOriginalStartingLine();
            }
            return $a->getOriginalFilePath() <=> $b->getOriginalFilePath();
        });
    }
}
