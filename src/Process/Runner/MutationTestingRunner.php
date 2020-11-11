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
use Infection\Process\Factory\MutantProcessFactory;
use function Pipeline\take;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 * @final
 */
class MutationTestingRunner
{
    private MutantProcessFactory $processFactory;
    private MutantFactory $mutantFactory;
    private ProcessRunner $processRunner;
    private EventDispatcher $eventDispatcher;
    private Filesystem $fileSystem;
    private DiffSourceCodeMatcher $diffSourceCodeMatcher;
    private bool $runConcurrently;
    private float $timeout;
    /** @var array<string, array<int, string>> */
    private array $ignoreSourceCodeMutatorsMap;

    /**
     * @param array<string, array<int, string>> $ignoreSourceCodeMutatorsMap
     */
    public function __construct(
        MutantProcessFactory $processFactory,
        MutantFactory $mutantFactory,
        ProcessRunner $processRunner,
        EventDispatcher $eventDispatcher,
        Filesystem $fileSystem,
        DiffSourceCodeMatcher $diffSourceCodeMatcher,
        bool $runConcurrently,
        float $timeout,
        array $ignoreSourceCodeMutatorsMap
    ) {
        $this->processFactory = $processFactory;
        $this->mutantFactory = $mutantFactory;
        $this->processRunner = $processRunner;
        $this->eventDispatcher = $eventDispatcher;
        $this->fileSystem = $fileSystem;
        $this->diffSourceCodeMatcher = $diffSourceCodeMatcher;
        $this->runConcurrently = $runConcurrently;
        $this->timeout = $timeout;
        $this->ignoreSourceCodeMutatorsMap = $ignoreSourceCodeMutatorsMap;
    }

    /**
     * @param iterable<Mutation> $mutations
     */
    public function run(iterable $mutations, string $testFrameworkExtraOptions): void
    {
        $numberOfMutants = IterableCounter::bufferAndCountIfNeeded($mutations, $this->runConcurrently);
        $this->eventDispatcher->dispatch(new MutationTestingWasStarted($numberOfMutants));

        $processes = take($mutations)
            ->cast(function (Mutation $mutation): Mutant {
                return $this->mutantFactory->create($mutation);
            })
            ->filter(function (Mutant $mutant) {
                // It's a proxy call to Mutation, can be done one stage up
                if ($mutant->isCoveredByTest()) {
                    return true;
                }

                $this->eventDispatcher->dispatch(new MutantProcessWasFinished(
                    MutantExecutionResult::createFromNonCoveredMutant($mutant)
                ));

                return false;
            })
            ->filter(function (Mutant $mutant) {
                $mutatorName = $mutant->getMutation()->getMutatorName();

                if (!array_key_exists($mutatorName, $this->ignoreSourceCodeMutatorsMap)) {
                    return true;
                }

                foreach ($this->ignoreSourceCodeMutatorsMap[$mutatorName] as $sourceCodeRegex) {
                    if ($this->diffSourceCodeMatcher->matches($mutant->getDiff()->get(), $sourceCodeRegex)) {
                        return false;
                    }
                }

                return true;
            })
            ->filter(function (Mutant $mutant) {
                // TODO refactor this comparison into a dedicated comparer to make it possible to swap strategies
                if ($mutant->getMutation()->getNominalTestExecutionTime() < $this->timeout) {
                    return true;
                }

                $this->eventDispatcher->dispatch(new MutantProcessWasFinished(
                    MutantExecutionResult::createFromTimeSkippedMutant($mutant)
                ));

                return false;
            })
            ->cast(function (Mutant $mutant) use ($testFrameworkExtraOptions): ProcessBearer {
                $this->fileSystem->dumpFile($mutant->getFilePath(), $mutant->getMutatedCode()->get());

                $process = $this->processFactory->createProcessForMutant($mutant, $testFrameworkExtraOptions);

                return $process;
            })
        ;

        $this->processRunner->run($processes);

        $this->eventDispatcher->dispatch(new MutationTestingWasFinished());
    }
}
