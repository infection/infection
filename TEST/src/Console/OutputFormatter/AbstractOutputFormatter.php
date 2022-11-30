<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Console\OutputFormatter;

use _HumbugBox9658796bb9f0\Infection\Mutant\MutantExecutionResult;
abstract class AbstractOutputFormatter implements OutputFormatter
{
    public const UNKNOWN_COUNT = 0;
    protected int $callsCount = 0;
    public function start(int $mutationCount) : void
    {
        $this->callsCount = 0;
    }
    public function advance(MutantExecutionResult $executionResult, int $mutationCount) : void
    {
        ++$this->callsCount;
    }
    public function finish() : void
    {
    }
}
