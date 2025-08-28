<?php

declare(strict_types=1);

namespace Infection\Telemetry\Reporter;

use Infection\Telemetry\Tracing\Trace;

interface TraceReporter
{
    public function report(Trace $trace): void;
}
