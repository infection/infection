<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Console\OutputFormatter;

use _HumbugBox9658796bb9f0\Infection\Mutant\MutantExecutionResult;
interface OutputFormatter
{
    public function start(int $mutationCount) : void;
    public function advance(MutantExecutionResult $executionResult, int $mutationCount) : void;
    public function finish() : void;
}
