<?php

declare(strict_types=1);

namespace Infection\Telemetry\Reporter;

use Infection\Telemetry\Tracing\Trace;

interface TraceProvider
{
    public function getTrace(): Trace;
}
