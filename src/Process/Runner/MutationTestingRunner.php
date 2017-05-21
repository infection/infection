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
        /** @var MutantProcess[] $processes */
        $processes = [];

        foreach ($this->mutations as $mutation) {
            $mutant = $this->mutantCreator->create($mutation, $codeCoverageData);

            $processes[] = $this->processBuilder->getProcessForMutant($mutant);
        }

        $testFrameworkAdapter = $this->processBuilder->getTestFrameworkAdapter();

        // run multiple processes
        $mutantCount = count($this->mutations);
        $escapedCount = 0;
        $killedCount = 0;
        $timedOut = 0;
        $notCoveredByTests = 0;

        $this->eventDispatcher->dispatch(new MutationTestingStarted($mutantCount));

        $this->parallelProcessManager->run($processes, $threadCount);

        $this->eventDispatcher->dispatch(new MutationTestingFinished());

        foreach ($processes as $process) {
            if (! $process->getMutant()->isCoveredByTest()) {
                $notCoveredByTests++;
            } else if ($testFrameworkAdapter->testsPass($process->getProcess()->getOutput())) {
                $escapedCount++;

                echo $process->getMutant()->getMutation()->getOriginalFilePath() . "\n";
                echo $process->getMutant()->getDiff() . "\n";
                echo $process->getProcess()->getOutput() . "\n";

            } else if ($process->isTimedOut()) {
                $timedOut++;
            } else {
                $killedCount++;
            }
        }

        var_dump(sprintf(
            'Mutant count: %s. Killed: %s. Escaped: %s. Not Covered: %s',
            $mutantCount,
            $killedCount,
            $escapedCount,
            $notCoveredByTests
        ));
    }
}