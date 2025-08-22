<?php

declare(strict_types=1);

namespace newSrc\TestFramework\Adapter\PHPUnit;

use newSrc\TestFramework\Trace\Symbol\Symbol;
use newSrc\TestFramework\Tracing\Tracer;

final class PHPUnitTracer implements Tracer
{
    public function __construct(

    ) {
    }

    public function hasTests(Symbol $symbol): bool
    {
        // TODO: Implement hasTests() method.
    }
}
