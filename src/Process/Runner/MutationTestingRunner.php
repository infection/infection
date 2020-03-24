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

use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\MutantProcessWasFinished;
use Infection\Event\MutationTestingWasFinished;
use Infection\Event\MutationTestingWasStarted;
use Infection\IterableCounter;
use Infection\Mutant\Mutant;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutation\Mutation;
use Infection\Process\Builder\MutantProcessBuilder;
use function Pipeline\take;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class MutationTestingRunner
{
    private $processBuilder;
    private $processRunner;
    private $eventDispatcher;
    private $fileSystem;
    private $runConcurrently;

    public function __construct(
        MutantProcessBuilder $mutantProcessBuilder,
        ProcessRunner $processRunner,
        EventDispatcher $eventDispatcher,
        Filesystem $fileSystem,
        bool $runConcurrently
    ) {
        $this->processBuilder = $mutantProcessBuilder;
        $this->processRunner = $processRunner;
        $this->eventDispatcher = $eventDispatcher;
        $this->fileSystem = $fileSystem;
        $this->runConcurrently = $runConcurrently;
    }

    /**
     * @param iterable<Mutation> $mutations
     */
    public function run(iterable $mutations, string $testFrameworkExtraOptions): void
    {
        $numberOfMutants = IterableCounter::bufferAndCountIfNeeded($mutations, $this->runConcurrently);
        $this->eventDispatcher->dispatch(new MutationTestingWasStarted($numberOfMutants));

        $processes = take($mutations)
            ->filter(function (Mutation $mutation) {
                // The filtering is done here since with a mutant and not earlier with a mutation
                // since:
                // - if pass the filtering, the mutant is going to be used
                // - if does not pass the filtering, the mutant is used for the reports
                if ($mutation->hasTests()) {
                    return true;
                }

                $this->eventDispatcher->dispatch(new MutantProcessWasFinished(
                    MutantExecutionResult::createFromNonCoveredMutant($mutation)
                ));

                return false;
            })
            ->map(function (Mutation $mutation) use ($testFrameworkExtraOptions): ProcessBearer {
                $this->fileSystem->dumpFile($mutation->getFilePath(), $mutation->getMutatedCode());

                $process = $this->processBuilder->createProcessForMutant($mutation, $testFrameworkExtraOptions);

                return $process;
            })
        ;

        $this->processRunner->run($processes);

        $this->eventDispatcher->dispatch(new MutationTestingWasFinished());
    }
}
