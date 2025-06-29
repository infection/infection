<?php

declare(strict_types=1);

namespace newSrc\MutationAnalyzer\MutantExecutor;

use newSrc\MutationAnalyzer\Mutant;
use newSrc\MutationAnalyzer\MutantExecutionResult;

interface MutantExecutor
{
    /**
     * @return iterable<MutantExecutionResult>
     */
    public function execute(Mutant $mutant): MutantExecutionResult;
}
