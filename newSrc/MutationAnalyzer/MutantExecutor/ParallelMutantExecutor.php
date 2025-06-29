<?php

declare(strict_types=1);

namespace newSrc\MutationAnalyzer\MutantExecutor;

use newSrc\MutationAnalyzer\Mutant;
use newSrc\MutationAnalyzer\MutantExecutionResult;
use newSrc\MutationAnalyzer\MutantExecutionStatus;
use newSrc\TestFramework\TestFramework;

final class ParallelMutantExecutor implements MutantExecutor
{
    /**
     * @param TestFramework[] $testFrameworks
     */
    public function __construct(
        private array $testFrameworks,
    ) {
    }

    public function execute(Mutant $mutant): MutantExecutionResult
    {
        // TODO: adds the mutant to the bucket
    }
}
