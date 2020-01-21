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
use Infection\Event\EventDispatcher;
use Infection\Event\MutantCreated;
use Infection\Event\MutantsCreatingFinished;
use Infection\Event\MutantsCreatingStarted;
use Infection\Event\MutationTestingFinished;
use Infection\Event\MutationTestingStarted;
use Infection\Mutagen\Mutant\MutantFactory;
use Infection\Mutagen\Mutation\Mutation;
use Infection\Process\Builder\MutantProcessBuilder;
use Infection\Process\MutantProcess;
use Infection\Process\Runner\Parallel\ParallelProcessRunner;

/**
 * @internal
 */
final class MutationTestingRunner
{
    private $processBuilder;
    private $mutantCreator;
    private $parallelProcessManager;
    private $eventDispatcher;

    public function __construct(
        MutantProcessBuilder $processBuilder,
        ParallelProcessRunner $parallelProcessManager,
        MutantFactory $mutantCreator,
        EventDispatcher $eventDispatcher
    ) {
        $this->processBuilder = $processBuilder;
        $this->mutantCreator = $mutantCreator;
        $this->parallelProcessManager = $parallelProcessManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Mutation[] $mutations
     */
    public function run(array $mutations, int $threadCount, string $testFrameworkExtraOptions): void
    {
        $mutantCount = count($mutations);

        $this->eventDispatcher->dispatch(new MutantsCreatingStarted($mutantCount));

        $processes = array_map(
            function (Mutation $mutation) use ($testFrameworkExtraOptions): MutantProcess {
                $mutant = $this->mutantCreator->create($mutation);

                $process = $this->processBuilder->createProcessForMutant($mutant, $testFrameworkExtraOptions);

                $this->eventDispatcher->dispatch(new MutantCreated());

                return $process;
            },
            $mutations
        );

        $this->eventDispatcher->dispatch(new MutantsCreatingFinished());

        $this->eventDispatcher->dispatch(new MutationTestingStarted($mutantCount));

        $this->parallelProcessManager->run($processes, $threadCount);

        $this->eventDispatcher->dispatch(new MutationTestingFinished());
    }
}
