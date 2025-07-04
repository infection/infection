<?php

declare(strict_types=1);

namespace newSrc\InitialRun;

use Infection\Event\EventDispatcher\EventDispatcher;

final class InitialExecutionRunner
{
    /**
     * @param InitialTestFrameworkRunner[] $runners
     */
    public function __construct(
        private array $runners,
        private EventDispatcher $eventDispatcher,
    ) {
    }

    public function run(): void {
        $this->eventDispatcher->dispatch(new InitialExecutionStarted());

        foreach ($this->runners as $runner) {
            $runner->run();
        }

        $this->eventDispatcher->dispatch(new InitialExecutionFinished());
    }
}