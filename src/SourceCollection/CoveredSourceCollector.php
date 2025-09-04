<?php

declare(strict_types=1);

namespace Infection\SourceCollection;

use Infection\Tracing\Tracer;

final readonly class CoveredSourceCollector implements SourceCollector
{
    public function __construct(
        private SourceCollector $decoratedSourceCollector,
        private Tracer $tracer,
    ) {

    }

    public function collect(): iterable
    {
        // TODO: preserve the key?
        foreach ($this->decoratedSourceCollector->collect() as $source) {
            if ($this->tracer->hasTrace($source)) {
                yield $source;
            }
        }
    }
}
