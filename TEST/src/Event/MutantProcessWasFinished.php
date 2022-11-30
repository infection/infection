<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Event;

use _HumbugBox9658796bb9f0\Infection\Mutant\MutantExecutionResult;
class MutantProcessWasFinished
{
    public function __construct(private MutantExecutionResult $executionResult)
    {
    }
    public function getExecutionResult() : MutantExecutionResult
    {
        return $this->executionResult;
    }
}
