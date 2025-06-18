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
use Infection\Event\MutantProcessWasFinished;
use Infection\Event\MutationTestingWasFinished;
use Infection\Event\MutationTestingWasStarted;
use Infection\IterableCounter;
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
        $this->eventDispatcher->dispatch(new MutationTestingWasStarted($numberOfMutants, $this->processRunner));

        $processContainers = take($mutations)
            ->stream()
            ->filter($this->ignoredByMutantId(...))
            ->cast($this->mutationToMutant(...))
            ->filter($this->ignoredByRegex(...))
            ->filter($this->uncoveredByTest(...))
            ->filter($this->takingTooLong(...))
            ->cast(fn (Mutant $mutant) => $this->mutantToContainer($mutant, $testFrameworkExtraOptions))
        ;

        take($this->processRunner->run($processContainers))
            ->cast(self::containerToFinishedEvent(...))
            ->each($this->eventDispatcher->dispatch(...))
        ;

        $this->eventDispatcher->dispatch(new MutationTestingWasFinished());
    }

    private function mutationToMutant(Mutation $mutation): Mutant
    {
        return $this->mutantFactory->create($mutation);
    }

    private function ignoredByMutantId(Mutation $mutation): bool
    {
        if ($this->mutantId === null) {
            return true;
        }

        return $mutation->getHash() === $this->mutantId;
    }

    private function ignoredByRegex(Mutant $mutant): bool
    {
        $mutatorName = $mutant->getMutation()->getMutatorName();

        if (!array_key_exists($mutatorName, $this->ignoreSourceCodeMutatorsMap)) {
            return true;
        }

        foreach ($this->ignoreSourceCodeMutatorsMap[$mutatorName] as $sourceCodeRegex) {
            if (!$this->diffSourceCodeMatcher->matches($mutant->getDiff()->get(), $sourceCodeRegex)) {
                continue;
            }

            $this->eventDispatcher->dispatch(new MutantProcessWasFinished(
                MutantExecutionResult::createFromIgnoredMutant($mutant),
            ));

            return false;
        }

        return true;
    }

    private function uncoveredByTest(Mutant $mutant): bool
    {
        // It's a proxy call to Mutation, can be done one stage up
        if ($mutant->isCoveredByTest()) {
            return true;
        }

        $this->eventDispatcher->dispatch(new MutantProcessWasFinished(
            MutantExecutionResult::createFromNonCoveredMutant($mutant),
        ));

        return false;
    }

    private function takingTooLong(Mutant $mutant): bool
    {
        // TODO refactor this comparison into a dedicated comparer to make it possible to swap strategies
        if ($mutant->getMutation()->getNominalTestExecutionTime() < $this->timeout) {
            return true;
        }

        $this->eventDispatcher->dispatch(new MutantProcessWasFinished(
            MutantExecutionResult::createFromTimeSkippedMutant($mutant),
        ));

        return false;
    }

    private function mutantToContainer(Mutant $mutant, string $testFrameworkExtraOptions): MutantProcessContainer
    {
        $this->fileSystem->dumpFile($mutant->getFilePath(), $mutant->getMutatedCode()->get());

        return $this->processFactory->create($mutant, $testFrameworkExtraOptions);
    }

    private static function containerToFinishedEvent(MutantProcessContainer $container): MutantProcessWasFinished
    {
        return new MutantProcessWasFinished($container->getCurrent()->getMutantExecutionResult());
    }
}
