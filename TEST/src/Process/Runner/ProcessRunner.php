<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Process\Runner;

interface ProcessRunner
{
    public function run(iterable $processes) : void;
}
