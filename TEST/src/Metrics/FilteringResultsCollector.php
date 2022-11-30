<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Metrics;

use function array_filter;
use function array_key_exists;
use _HumbugBox9658796bb9f0\Infection\Mutant\MutantExecutionResult;
class FilteringResultsCollector implements Collector
{
    public function __construct(private Collector $targetCollector, private array $targetDetectionStatuses)
    {
    }
    public function collect(MutantExecutionResult ...$executionResults) : void
    {
        $executionResults = array_filter($executionResults, fn(MutantExecutionResult $executionResults): bool => array_key_exists($executionResults->getDetectionStatus(), $this->targetDetectionStatuses));
        if ($executionResults !== []) {
            $this->targetCollector->collect(...$executionResults);
        }
    }
}
