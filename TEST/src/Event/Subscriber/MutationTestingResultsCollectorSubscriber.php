<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Event\Subscriber;

use _HumbugBox9658796bb9f0\Infection\Event\MutantProcessWasFinished;
use _HumbugBox9658796bb9f0\Infection\Metrics\Collector;
final class MutationTestingResultsCollectorSubscriber implements EventSubscriber
{
    private array $collectors;
    public function __construct(Collector ...$collectors)
    {
        $this->collectors = $collectors;
    }
    public function onMutantProcessWasFinished(MutantProcessWasFinished $event) : void
    {
        $executionResult = $event->getExecutionResult();
        foreach ($this->collectors as $collector) {
            $collector->collect($executionResult);
        }
    }
}
