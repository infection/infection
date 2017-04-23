<?php
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

    public function run(int $threadCount) // TODO : MutationTestingResult
    {
        /** @var MutantProcess[] $processes */
        $processes = [];

        foreach ($this->mutations as $mutation) {
            $mutant = $this->mutantCreator->create($mutation);

            $processes[] = $this->processBuilder->getProcessForMutant($mutant);
        }

        $testFrameworkAdapter = $this->processBuilder->getTestFrameworkAdapter();

        // run multiple processes
        $mutantCount = count($this->mutations);
        $escapedCount = 0;
        $killedCount = 0;

        $this->eventDispatcher->dispatch(new MutationTestingStarted($mutantCount));

        // TODO add timeout handling like in humbug
        $this->parallelProcessManager->run($processes, $threadCount);

        $this->eventDispatcher->dispatch(new MutationTestingFinished());

        foreach ($processes as $process) {
            $processOutput = $process->getProcess()->getOutput();

            if ($testFrameworkAdapter->testsPass($processOutput)) {
                $escapedCount++;

                echo $process->getMutant()->getMutation()->getOriginalFilePath() . "\n";
                echo $process->getMutant()->getDiff() . "\n";
                echo $processOutput . "\n";

            } else {
                $killedCount++;
            }
        }

        var_dump(sprintf(
            'Mutant count: %s. Killed: %s. Escaped: %s',
            $mutantCount,
            $killedCount,
            $escapedCount
        ));
    }
}