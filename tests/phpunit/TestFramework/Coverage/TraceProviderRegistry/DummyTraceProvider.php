<?php

declare(strict_types=1);

namespace Infection\Tests\TestFramework\Coverage\TraceProviderRegistry;

use Infection\TestFramework\Coverage\Trace;
use Infection\TestFramework\Coverage\TraceProvider;

final readonly class DummyTraceProvider implements TraceProvider
{
    /**
     * @param Trace $traces
     */
    public function __construct(
        private array $traces,
    ) {
    }

    public function provideTraces(): iterable
    {
        yield from $this->traces;
    }
}