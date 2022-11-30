<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Metrics;

use _HumbugBox9658796bb9f0\Infection\Mutant\MutantExecutionResult;
interface Collector
{
    public function collect(MutantExecutionResult ...$executionResults) : void;
}
