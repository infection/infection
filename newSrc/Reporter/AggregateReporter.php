<?php

declare(strict_types=1);

namespace newSrc\Reporter;

use newSrc\Engine\Envelope;
use newSrc\MutationAnalyzer\MutantExecutionResult;

final class AggregateReporter implements Reporter
{
    /**
     * @param Reporter[] $reporters
     */
    public function __construct(
        private array $reporters,
    ) {
    }

    public function collect(MutantExecutionResult $result, Envelope $envelope): void
    {
        foreach ($this->reporters as $reporter) {
            $reporter->collect($result, $envelope);
        }
    }

    public function report(): void
    {
        foreach ($this->reporters as $reporter) {
            $reporter->report();
        }
    }
}
