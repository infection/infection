<?php

namespace Infection\Telemetry\Metric\Time;

interface Stopwatch
{
    public function current(): HRTime;
}
