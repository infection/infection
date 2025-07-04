<?php

declare(strict_types=1);

namespace newSrc\MutationAnalyzer;

use newSrc\Mutagenesis\Mutation;
use newSrc\MutationAnalyzer\AnalyzerHeuristic\AnalyzerHeuristic;
use newSrc\MutationAnalyzer\MutantExecutor\MutantExecutor;

final readonly class MutationAnalyzer
{
    /**
     * @param AnalyzerHeuristic[] $heuristics
     */
    public function __construct(
        private array $heuristics,
        private MutantFactory $mutantFactory,
        private MutantExecutor $mutantExecutor,
    ) {
    }

    /**
     * @return iterable<MutantExecutionResult>
     */
    public function analyze(Mutation $mutation): iterable
    {
        foreach ($this->heuristics as $heuristic) {
            $result = $heuristic->analyze($mutation);

            if (null !== $result) {
                yield $result;

                return;
            }
        }

        $mutant = $this->mutantFactory->create($mutation);

        yield $this->mutantExecutor->execute($mutant);
    }
}
