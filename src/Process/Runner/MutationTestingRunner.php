<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Process\Runner;

use function array_key_exists;
use Infection\Differ\DiffSourceCodeMatcher;
use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\HeuristicSuppression\HeuristicSuppressionWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\HeuristicSuppression\HeuristicSuppressionWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\HeuristicSuppression\HeuristicWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\HeuristicSuppression\HeuristicWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantAnalysisWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantAnalysisWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantEvaluation\MutantEvaluationWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantEvaluation\MutantEvaluationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantMaterialisation\MutantMaterialisationWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantMaterialisation\MutantMaterialisationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationEvaluationForMutationWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationEvaluationForMutationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluationWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluationWasStarted;
use Infection\Framework\Iterable\IterableCounter;
use Infection\Mutant\Mutant;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutant\MutantFactory;
use Infection\Mutation\Mutation;
use Infection\Process\Factory\MutantProcessContainerFactory;
use Infection\Process\MutantProcessContainer;
use function Pipeline\take;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 * @final
 */
class MutationTestingRunner
{
    /**
     * @param array<string, array<int, string>> $ignoreSourceCodeMutatorsMap
     */
    public function __construct(
        private readonly MutantProcessContainerFactory $processFactory,
        private readonly MutantFactory $mutantFactory,
        private readonly ProcessRunner $processRunner,
        private readonly EventDispatcher $eventDispatcher,
        private readonly Filesystem $fileSystem,
        private readonly DiffSourceCodeMatcher $diffSourceCodeMatcher,
        private readonly bool $runConcurrently,
        private readonly float $timeout,
        private readonly array $ignoreSourceCodeMutatorsMap,
        private readonly ?string $mutantId = null,
    ) {
    }

    /**
     * @param iterable<Mutation> $mutations
     */
    public function run(iterable $mutations, string $testFrameworkExtraOptions): void
    {
        $numberOfMutants = IterableCounter::bufferAndCountIfNeeded($mutations, $this->runConcurrently);
        $this->eventDispatcher->dispatch(new MutationEvaluationWasStarted($numberOfMutants, $this->processRunner));

        $processContainers = take($mutations)
            ->stream()
            ->filter($this->ignoredByMutantId(...))
            // Emitting the start of the event must be done _after_ checking the mutant ID
            // as the latter does not dispatch any finished event.
            ->tap($this->emitEvaluationStarted(...))
            ->cast($this->mutationToMutant(...))
            ->tap($this->emitHeuristicSuppressionStarted(...))
            ->filter($this->ignoredByRegex(...))
            ->filter($this->uncoveredByTest(...))
            ->filter($this->takingTooLong(...))
            ->tap($this->emitHeuristicSuppressionFinished(...))
            ->cast(fn (Mutant $mutant) => $this->mutantToContainer($mutant, $testFrameworkExtraOptions))
        ;

        take($this->processRunner->run($this->markMutantEvaluationStarted($processContainers)))
            ->tap($this->emitMutantEvaluationFinished(...))
            ->cast(self::containerToFinishedEvent(...))
            ->each($this->eventDispatcher->dispatch(...))
        ;

        $this->eventDispatcher->dispatch(new MutationEvaluationWasFinished());
    }

    private function mutationToMutant(Mutation $mutation): Mutant
    {
        return $this->mutantFactory->create($mutation);
    }

    private function emitEvaluationStarted(Mutation $mutation): void
    {
        $this->eventDispatcher->dispatch(
            new MutationEvaluationForMutationWasStarted($mutation),
        );
    }

    private function ignoredByMutantId(Mutation $mutation): bool
    {
        if ($this->mutantId === null) {
            return true;
        }

        if ($mutation->getHash() === $this->mutantId) {
            return true;
        }

        $this->eventDispatcher->dispatch(new HeuristicSuppressionWasStarted($mutation));
        $this->eventDispatcher->dispatch(new HeuristicWasStarted($mutation, MutationEvaluationHeuristic::IGNORED_BY_MUTATION_ID));
        $this->eventDispatcher->dispatch(new HeuristicWasFinished($mutation, MutationEvaluationHeuristic::IGNORED_BY_MUTATION_ID));
        $this->eventDispatcher->dispatch(new HeuristicSuppressionWasFinished($mutation));

        return false;
    }

    private function ignoredByRegex(Mutant $mutant): bool
    {
        $isKept = $this->evaluateHeuristic(
            $mutant,
            MutationEvaluationHeuristic::IGNORED_BY_REGEX,
            function (Mutant $mutant): bool {
                $mutatorName = $mutant->getMutation()->getMutatorName();

                if (!array_key_exists($mutatorName, $this->ignoreSourceCodeMutatorsMap)) {
                    return true;
                }

                foreach ($this->ignoreSourceCodeMutatorsMap[$mutatorName] as $sourceCodeRegex) {
                    if (!$this->diffSourceCodeMatcher->matches($mutant->getDiff()->get(), $sourceCodeRegex)) {
                        continue;
                    }

                    return false;
                }

                return true;
            },
        );

        if (!$isKept) {
            $this->emitHeuristicSuppressionFinished($mutant);
            $this->eventDispatcher->dispatch(new MutationEvaluationForMutationWasFinished(
                MutantExecutionResult::createFromIgnoredMutant($mutant),
            ));
        }

        return $isKept;
    }

    private function uncoveredByTest(Mutant $mutant): bool
    {
        $isKept = $this->evaluateHeuristic(
            $mutant,
            MutationEvaluationHeuristic::UNCOVERED_BY_TESTS,
            static function (Mutant $mutant): bool {
                // It's a proxy call to Mutation, can be done one stage up
                return $mutant->isCoveredByTest();
            },
        );

        if (!$isKept) {
            $this->emitHeuristicSuppressionFinished($mutant);
            $this->eventDispatcher->dispatch(new MutationEvaluationForMutationWasFinished(
                MutantExecutionResult::createFromNonCoveredMutant($mutant),
            ));
        }

        return $isKept;
    }

    private function takingTooLong(Mutant $mutant): bool
    {
        $isKept = $this->evaluateHeuristic(
            $mutant,
            MutationEvaluationHeuristic::TAKING_TOO_LONG,
            function (Mutant $mutant): bool {
                // TODO refactor this comparison into a dedicated comparer to make it possible to swap strategies
                return $mutant->getMutation()->getNominalTestExecutionTime() < $this->timeout;
            },
        );

        if (!$isKept) {
            $this->emitHeuristicSuppressionFinished($mutant);
            $this->eventDispatcher->dispatch(new MutationEvaluationForMutationWasFinished(
                MutantExecutionResult::createFromTimeSkippedMutant($mutant),
            ));
        }

        return $isKept;
    }

    private function mutantToContainer(Mutant $mutant, string $testFrameworkExtraOptions): MutantProcessContainer
    {
        $this->eventDispatcher->dispatch(new MutantAnalysisWasStarted($mutant));
        $this->eventDispatcher->dispatch(new MutantMaterialisationWasStarted($mutant));

        try {
            $this->fileSystem->dumpFile($mutant->getFilePath(), $mutant->getMutatedCode()->get());

            return $this->processFactory->create($mutant, $testFrameworkExtraOptions);
        } finally {
            $this->eventDispatcher->dispatch(new MutantMaterialisationWasFinished($mutant));
        }
    }

    private static function containerToFinishedEvent(MutantProcessContainer $container): MutationEvaluationForMutationWasFinished
    {
        return new MutationEvaluationForMutationWasFinished($container->getCurrent()->getMutantExecutionResult());
    }

    private function emitHeuristicSuppressionStarted(Mutant $mutant): void
    {
        $this->eventDispatcher->dispatch(new HeuristicSuppressionWasStarted($mutant->getMutation()));
    }

    private function emitHeuristicSuppressionFinished(Mutant $mutant): void
    {
        $this->eventDispatcher->dispatch(new HeuristicSuppressionWasFinished($mutant->getMutation()));
    }

    /**
     * @param callable(Mutant): bool $heuristic
     */
    private function evaluateHeuristic(
        Mutant $mutant,
        MutationEvaluationHeuristic $heuristicName,
        callable $heuristic,
    ): bool {
        $mutation = $mutant->getMutation();

        $this->eventDispatcher->dispatch(new HeuristicWasStarted($mutation, $heuristicName));

        try {
            return $heuristic($mutant);
        } finally {
            $this->eventDispatcher->dispatch(new HeuristicWasFinished($mutation, $heuristicName));
        }
    }

    /**
     * @param iterable<MutantProcessContainer> $processContainers
     *
     * @return iterable<MutantProcessContainer>
     */
    private function markMutantEvaluationStarted(iterable $processContainers): iterable
    {
        foreach ($processContainers as $processContainer) {
            $this->eventDispatcher->dispatch(new MutantEvaluationWasStarted($processContainer->getCurrent()->getMutant()));

            yield $processContainer;
        }
    }

    private function emitMutantEvaluationFinished(MutantProcessContainer $processContainer): void
    {
        $mutant = $processContainer->getCurrent()->getMutant();

        $this->eventDispatcher->dispatch(new MutantEvaluationWasFinished($mutant));
        $this->eventDispatcher->dispatch(new MutantAnalysisWasFinished($mutant));
    }
}
