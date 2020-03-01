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

use function count;
use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\MutantProcessWasFinished;
use Infection\Event\MutationTestingWasFinished;
use Infection\Event\MutationTestingWasStarted;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutant\MutantFactory;
use Infection\Mutation\Mutation;
use Infection\Process\Builder\MutantProcessBuilder;
use Infection\Process\MutantProcess;
use Infection\Process\Runner\Parallel\ParallelProcessRunner;
use function Pipeline\take;

/**
 * @internal
 */
final class MutationTestingRunner
{
    private $mutantFactory;
    private $parallelProcessManager;
    private $eventDispatcher;
    private $processBuilder;
    private $runConcurrently;

    public function __construct(
        MutantProcessBuilder $mutantProcessBuilder,
        MutantFactory $mutantFactory,
        ParallelProcessRunner $parallelProcessManager,
        EventDispatcher $eventDispatcher,
        bool $runConcurrently
    ) {
        $this->processBuilder = $mutantProcessBuilder;
        $this->mutantFactory = $mutantFactory;
        $this->parallelProcessManager = $parallelProcessManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->runConcurrently = $runConcurrently;
    }

    /**
     * @param iterable<Mutation> $mutations
     */
    public function run(iterable $mutations, int $threadCount, string $testFrameworkExtraOptions): void
    {
        $numberOfMutants = $this->bufferAndCountIfNeeded($mutations);
        $this->eventDispatcher->dispatch(new MutationTestingWasStarted($numberOfMutants));

        $processes = take($mutations)
            ->map(function (Mutation $mutation) use ($testFrameworkExtraOptions): MutantProcess {
                $mutant = $this->mutantFactory->create($mutation);

                $process = $this->processBuilder->createProcessForMutant($mutant, $testFrameworkExtraOptions);

                return $process;
            })
            ->filter(function (MutantProcess $mutantProcess) {
                if ($mutantProcess->getMutant()->isCoveredByTest()) {
                    return true;
                }

                $this->eventDispatcher->dispatch(new MutantProcessWasFinished(
                    MutantExecutionResult::createFromProcess($mutantProcess)
                ));

                return false;
            });

        $this->parallelProcessManager->run($processes, $threadCount);

        $this->eventDispatcher->dispatch(new MutationTestingWasFinished());
    }

    /**
     * @param iterable<mixed> $subjects
     */
    private function bufferAndCountIfNeeded(iterable &$subjects): int
    {
        if ($this->runConcurrently) {
            return 0;
        }

        $buffer = [];

        // iterator_to_array wants \Traversable, we can have anything else
        foreach ($subjects as $subject) {
            $buffer[] = $subject;
            // TODO in PHP 7.4 use [...$subjects];
        }

        $subjects = $buffer;

        return count($subjects);
    }
}
