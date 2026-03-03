<?php

declare(strict_types=1);

namespace Infection\Reporter;

use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\Events\Reporting\ReportingWasFinished;
use Infection\Event\Events\Reporting\ReportingWasStarted;

final readonly class EventDispatchingReporter implements Reporter
{
    public function __construct(
        private Reporter $decoratedReporter,
        private EventDispatcher $eventDispatcher,
    ) {
    }

    public function report(): void
    {
        $this->eventDispatcher->dispatch(
            new ReportingWasStarted(),
        );

        $this->decoratedReporter->report();

        $this->eventDispatcher->dispatch(
            new ReportingWasFinished(),
        );
    }
}