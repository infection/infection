<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage;

final class UnionTraceProvider implements TraceProvider
{
    public function __construct(private TraceProvider $coveredTraceProvider, private TraceProvider $uncoveredTraceProvider, private bool $onlyCovered)
    {
    }
    public function provideTraces() : iterable
    {
        yield from $this->coveredTraceProvider->provideTraces();
        if ($this->onlyCovered === \false) {
            yield from $this->uncoveredTraceProvider->provideTraces();
        }
    }
}
