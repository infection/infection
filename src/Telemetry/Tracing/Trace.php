<?php

declare(strict_types=1);

namespace Infection\Telemetry\Tracing;

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
}