<?php
declare(strict_types=1);


namespace Infection\Process\Runner;


use Infection\Mutant\Mutant;
use Infection\Mutant\MutantFileCreator;
use Infection\Mutation;
use Infection\Process\Builder\ProcessBuilder;
use Symfony\Component\Process\Process;

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
     * @var MutantFileCreator
     */
    private $mutantFileCreator;

    public function __construct(ProcessBuilder $processBuilder, MutantFileCreator $mutantFileCreator, array $mutations)
    {
        $this->processBuilder = $processBuilder;
        $this->mutations = $mutations;
        $this->mutantFileCreator = $mutantFileCreator;
    }

    public function run() // TODO : MutationTestingResult
    {
        /** @var Process[] $processes */
        $processes = [];

        foreach ($this->mutations as $mutation) {
            // generate process

            $mutatedFilePath = $this->mutantFileCreator->create($mutation);
            $mutant = new Mutant(
                $mutatedFilePath,
                $mutation
            );

            $processes[] = $this->processBuilder->getProcessForMutant($mutant);
        }

        // run multiple processes
        foreach ($processes as $process) {
            $process->run();

            echo $process->getOutput();
        }
    }
}