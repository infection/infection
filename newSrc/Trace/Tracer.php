<?php

namespace newSrc\Trace;

interface Tracer
{
    public function hasTests(string $symbol): bool;
}
