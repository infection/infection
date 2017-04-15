<?php
declare(strict_types=1);


namespace Infection\Process\Runner;

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

    public function __construct(ProcessBuilder $processBuilder, MutantCreator $mutantCreator, array $mutations)
    {
        $this->processBuilder = $processBuilder;
        $this->mutations = $mutations;
        $this->mutantCreator = $mutantCreator;
    }

    public function run($inParallel = false) // TODO : MutationTestingResult
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

        if ($inParallel) {
            $parallelProcessRunner = new ParallelProcessRunner($processes);
            $parallelProcessRunner->run();
        } else {
            foreach ($processes as $process) {
                $process->getProcess()->run();
            }
        }

        foreach ($processes as $process) {
            $processOutput = $process->getProcess()->getOutput();

            if ($testFrameworkAdapter->testsPass($processOutput)) {
                $escapedCount++;
            } else {
                $killedCount++;
            }

            echo $process->getMutant()->getDiff();
            echo $processOutput;
        }

        var_dump(sprintf(
            'Mutant count: %s. Killed: %s. Escaped: %s',
            $mutantCount,
            $killedCount,
            $escapedCount
        ));
    }
}