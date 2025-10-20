<?php

declare(strict_types=1);

namespace Infection\Telemetry\Tracing;

use Infection\Telemetry\Metric\ResourceInspector;
use Infection\Telemetry\Reporter\TraceProvider;
use Infection\Utility\UniqueId;
use function array_map;

final class Tracer implements TraceProvider
{
    /**
     * @var list<SpanBuilder>
     */
    private array $spans;

    /**
     * @var array<string, list<SpanBuilder>>
     */
    private array $allSpans;

    public function __construct(
        private readonly ResourceInspector $inspector,
    ) {
    }

    public function startSpan(
        RootScopes $scope,
        string|int|null $id = null,
    ): SpanBuilder
    {
        $span = new SpanBuilder(
            (string) $id ?? UniqueId::generate(),
            $scope->value,
            $this->inspector->snapshot(),
        );

        $this->spans[] = $span;
        $this->allSpans[$span->id][] = $span;

        return $span;
    }

    public function startChildSpan(
        string $scope,
        string|int $id,
        SpanBuilder $parent,
    ): SpanBuilder {
        $span = new SpanBuilder(
            (string) $id ?? UniqueId::generate(),
            $scope,
            $this->inspector->snapshot(),
        );
        $this->allSpans[$span->id][] = $span;

        $parent->addChild($span);

        return $span;
    }

    public function finishSpan(SpanBuilder ...$spans): void
    {
        $end = $this->inspector->snapshot();

        foreach ($spans as $span) {
            $span->finish($end);
        }
    }

    public function getTrace(): Trace
    {
        return new Trace(
            UniqueId::generate(),
            array_map(
                static fn (SpanBuilder $spanBuilder) => $spanBuilder->build(),
                $this->spans,
            ),
        );
    }
}
