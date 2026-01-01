<?php

declare(strict_types=1);

namespace Infection\MutationTesting\MutationAnalyzer;

use Infection\Mutant\MutantExecutionResult;
use Infection\Mutation\Mutation;

final readonly class MutationAnalyzer
{
    /**
     * @param AnalyzerHeuristic[] $heuristics
     */
    public function __construct(
        private array          $heuristics,
        private MutantFactory  $mutantFactory,
        private MutantExecutor $mutantExecutor,
    )
    {
    }

    /**
     * @return iterable<MutantExecutionResult>
     */
    public function analyze(Mutation $mutation): iterable
    {
        foreach ($this->heuristics as $heuristic) {
            $result = $heuristic->analyze($mutation);

            if ($result !== null) {
                yield $result;

                return;
            }
        }

        $mutant = $this->mutantFactory->create($mutation);

        yield $this->mutantExecutor->execute($mutant);
    }
}
