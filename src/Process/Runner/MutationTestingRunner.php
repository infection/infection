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

use Infection\EventDispatcher\EventDispatcherInterface;
use Infection\Events\MutantCreated;
use Infection\Events\MutantsCreatingFinished;
use Infection\Events\MutantsCreatingStarted;
use Infection\Events\MutationTestingFinished;
use Infection\Events\MutationTestingStarted;
use Infection\Mutant\MutantCreator;
use Infection\Mutation;
use Infection\MutationInterface;
use Infection\Process\Builder\MutantProcessBuilder;
use Infection\Process\MutantProcessInterface;
use Infection\Process\Runner\Parallel\ParallelProcessRunner;

/**
 * @internal
 */
final class MutationTestingRunner
{
    /**
     * @var MutantProcessBuilder
     */
    private $processBuilder;

    /**
     * @var Mutation[]
     */
    private $mutations;
    /**
     * @var MutantCreator
     */
    private $mutantCreator;
    /**
     * @var ParallelProcessRunner
     */
    private $parallelProcessManager;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(MutantProcessBuilder $processBuilder, ParallelProcessRunner $parallelProcessManager, MutantCreator $mutantCreator, EventDispatcherInterface $eventDispatcher, array $mutations)
    {
        $this->processBuilder = $processBuilder;
        $this->mutantCreator = $mutantCreator;
        $this->parallelProcessManager = $parallelProcessManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->mutations = $mutations;
    }

    public function run(int $threadCount, string $testFrameworkExtraOptions): void
    {
        $mutantCount = \count($this->mutations);

        $this->eventDispatcher->dispatch(new MutantsCreatingStarted($mutantCount));

        $processes = array_map(
            function (MutationInterface $mutation) use ($testFrameworkExtraOptions): MutantProcessInterface {
                $mutant = $this->mutantCreator->create($mutation);

                $process = $this->processBuilder->getProcessForMutant($mutant, $testFrameworkExtraOptions);

                $this->eventDispatcher->dispatch(new MutantCreated());

                return $process;
            },
            $this->mutations
        );

        $this->eventDispatcher->dispatch(new MutantsCreatingFinished());

        $this->eventDispatcher->dispatch(new MutationTestingStarted($mutantCount));

        $this->parallelProcessManager->run($processes, $threadCount);

        $this->eventDispatcher->dispatch(new MutationTestingFinished());
    }
}
