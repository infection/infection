<?php

declare(strict_types=1);

namespace Infection\Source\Collector;

use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\Events\SourceCollection\SourceCollectionWasFinished;
use Infection\Event\Events\SourceCollection\SourceCollectionWasStarted;
use function count;

final readonly class EventDispatchingSourceCollector implements SourceCollector
{
    public function __construct(
        private SourceCollector $decoratedSourceCollector,
        private EventDispatcher $eventDispatcher,
    ) {
    }

    public function collect(): array
    {
        $this->eventDispatcher->dispatch(
            new SourceCollectionWasStarted(),
        );

        $sources = $this->decoratedSourceCollector->collect();

        $this->eventDispatcher->dispatch(
            new SourceCollectionWasFinished(
                count($sources),
            ),
        );

        return $sources;
    }
}