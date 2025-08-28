<?php

declare(strict_types=1);

namespace Infection\Telemetry\Tracing;

use Infection\Telemetry\Metric\ResourceInspector;
use Infection\Telemetry\Metric\Snapshot;
use LogicException;
use function array_map;
use function count;
use function end;
use function sprintf;

final class SpanBuilder
{
    public readonly string $id;

    private Snapshot $end;

    /**
     * @var list<SpanBuilder>
     */
    private array $children = [];

    public function __construct(
        // The scope ID should be unique per scope, but the same ID can be
        // re-used across different scopes.
        private readonly string $scopeId,
        private readonly string $scope,
        private readonly Snapshot $start,
    ) {
        $this->id = $this->scope.':'.$this->scopeId;
    }

    /** @internal Should only be used by the Tracer */
    public function finish(Snapshot $end): void
    {
        $this->assertSpanWasNotFinished();

        $this->end = $end;
    }

    public function addChild(SpanBuilder $span): void {
        $this->children[] = $span;
    }

    public function build(): Span
    {
        if (count($this->children) > 0) {
            $this->end ??= $this->getChildrenLastSnapshot();
        }

        $this->assertSpanWasFinished();

        return new Span(
            $this->id,
            $this->scopeId,
            $this->scope,
            $this->start,
            $this->end,
            array_map(
                static fn (SpanBuilder $child) => $child->build(),
                $this->children,
            ),
        );
    }

    private function assertSpanWasFinished(): void
    {
        if (!isset($this->end)) {
            throw new LogicException(
                sprintf(
                    'The span "%s" for the scope "%s" was never finished.',
                    $this->scopeId,
                    $this->scope,
                ),
            );
        }
    }

    private function assertSpanWasNotFinished(): void
    {
        if (isset($this->end)) {
            throw new LogicException(
                sprintf(
                    'The span "%s" for the scope "%s" has already finished.',
                    $this->scopeId,
                    $this->scope,
                ),
            );
        }
    }

    private function getChildrenLastSnapshot(): Snapshot {
        return end($this->children)->end;
    }
}