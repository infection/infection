<?php

declare(strict_types=1);

namespace Infection\Telemetry\Metric\Time;

use function Safe\hrtime;

final class SystemStopwatch implements Stopwatch
{
    public function current(): HRTime
    {
        return HRTime::fromSecondsAndNanoseconds(...hrtime());
    }
}