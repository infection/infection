<?php

namespace Infection\Mutant;

use Infection\Process\MutantProcess;

interface MutantExecutionResultFactory
{
    public function createFromProcess(MutantProcess $mutantProcess): MutantExecutionResult;
}
