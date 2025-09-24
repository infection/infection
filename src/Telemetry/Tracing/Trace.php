<?php

declare(strict_types=1);

namespace Infection\Telemetry\Tracing;

use Infection\Telemetry\Metric\Time\Duration;
use function array_key_last;
use function current;
use function end;
use function unserialize;

final readonly class Trace
{
    /**
     * @param list<Span> $spans
     */
    public function __construct(
        public string $id,
        public array $spans,
    ) {
    }

    public static function unserialize(string $serializedTrace): Trace
    {
        return unserialize(
            $serializedTrace,
            // TODO: maybe we want to enable only some classes here
        );
    }

    public function getDuration(): Duration
    {
        /** @var Span $first */
        $first = current($this->spans);
        /** @var Span $last */
        $last = $this->spans[array_key_last($this->spans)];

        return $last->end->time->getDuration(
            $first->start->time,
        );
    }

    /**
     * @param list<Span> $spans
     */
    public function withSpans(array $spans): self
    {
        return new self($this->id, $spans);
    }
}
