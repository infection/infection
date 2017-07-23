<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Process\Runner;

use Infection\EventDispatcher\EventDispatcherInterface;
use Infection\Events\MutationTestingFinished;
use Infection\Events\MutationTestingStarted;
use Infection\Mutant\MutantCreator;
use Infection\Mutation;
use Infection\Process\Builder\ProcessBuilder;
use Infection\Process\MutantProcess;
use Infection\Process\Runner\Parallel\ParallelProcessRunner;
use Infection\TestFramework\Coverage\CodeCoverageData;

class MutationTestingRunner
{
    /**
     * @var ProcessBuilder
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

    public function __construct(ProcessBuilder $processBuilder, ParallelProcessRunner $parallelProcessManager, MutantCreator $mutantCreator, EventDispatcherInterface $eventDispatcher, array $mutations)
    {
        $this->processBuilder = $processBuilder;
        $this->mutantCreator = $mutantCreator;
        $this->parallelProcessManager = $parallelProcessManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->mutations = $mutations;
    }

    public function run(int $threadCount, CodeCoverageData $codeCoverageData)
    {
        $processes = array_map(
            function (Mutation $mutation) use ($codeCoverageData): MutantProcess {
                $mutant = $this->mutantCreator->create($mutation, $codeCoverageData);

                return $this->processBuilder->getProcessForMutant($mutant);
            },
            $this->mutations
        );

        // run multiple processes
        $mutantCount = count($this->mutations);

        $this->eventDispatcher->dispatch(new MutationTestingStarted($mutantCount));

        $this->parallelProcessManager->run($processes, $threadCount);

        $this->eventDispatcher->dispatch(new MutationTestingFinished());
    }
}
