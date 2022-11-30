<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\AbstractTestFramework;

interface MemoryUsageAware
{
    public function getMemoryUsed(string $output) : float;
}
