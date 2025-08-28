<?php

declare(strict_types=1);

namespace Infection\Command\Telemetry;

enum TraceFormat: string
{
    case TEXT = 'text';
    case CSV = 'csv';
}
