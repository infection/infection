<?php

namespace Infection\Tracing;

use Infection\TestFramework\Coverage\Trace;
use SplFileInfo;

interface Tracer
{
    public function trace(SplFileInfo $fileInfo): Trace;
}
