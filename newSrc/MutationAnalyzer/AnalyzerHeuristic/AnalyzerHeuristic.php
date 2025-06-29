<?php

namespace newSrc\MutationAnalyzer\AnalyzerHeuristic;

use newSrc\Mutagenesis\Mutation;
use newSrc\MutationAnalyzer\MutantExecutionResult;

interface AnalyzerHeuristic
{
    public function analyze(Mutation $mutation): ?MutantExecutionResult;
}
