<?php

declare(strict_types=1);

namespace Infection\MutationTesting\MutationAnalyzer\Heuristic;

use Infection\Mutant\MutantExecutionResult;
use Infection\Mutation\Mutation;

/**
 * A heuristic is a quick in-memory to attempt to determine the result of a mutation
 * without running a more expensive process to know if it is covered or not.
 */
interface Heuristic
{
    /**
     * If the returned value is `null`, it means the result could not be determined
     * and further (more expensive) evaluations must be done.
     */
    public function evaluate(Mutation $mutation): ?MutantExecutionResult;
}
