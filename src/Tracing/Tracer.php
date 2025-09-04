<?php

namespace Infection\Tracing;

use Infection\TestFramework\Coverage\Trace;
use SplFileInfo;

interface Tracer
{
    public function hasTrace(SplFileInfo $fileInfo): Trace;

    public function trace(SplFileInfo $fileInfo): Trace;
}
