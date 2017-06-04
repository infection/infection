<?php

declare(strict_types=1);

namespace Infection\Process\Builder;

use Infection\Mutant\Mutant;
use Infection\Process\MutantProcess;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

class ProcessBuilder
{
    /**
     * @var AbstractTestFrameworkAdapter
     */
    private $testFrameworkAdapter;

    /**
     * @var int
     */
    private $timeout;

    public function __construct(AbstractTestFrameworkAdapter $testFrameworkAdapter, int $timeout)
    {
        $this->testFrameworkAdapter = $testFrameworkAdapter;
        $this->timeout = $timeout;
    }

    public function build() : Process
    {
        $configPath = $this->testFrameworkAdapter->buildInitialConfigFile();

        return new Process(
            $this->testFrameworkAdapter->getExecutableCommandLine($configPath),
            null, // TODO make it dynamic to change testdir
            array_replace($_ENV, $_SERVER)
        );

        // TODO debug why processBuilder does not work with env
        /**
        $processBuilder = new SymfonyProcessBuilder([
        $this->testFrameworkAdapter->getExecutableCommandLine()
        ]);

        return $processBuilder->getProcess();
         */
    }

    /**
     * @throws RuntimeException
     * @param Mutant $mutant
     * @return MutantProcess
     */
    public function getProcessForMutant(Mutant $mutant) : MutantProcess
    {
        $configPath = $this->testFrameworkAdapter->buildMutationConfigFile($mutant);

        $symfonyProcess = new Process(
            $this->testFrameworkAdapter->getExecutableCommandLine($configPath),
            null,
            array_replace($_ENV, $_SERVER)
        );
        $symfonyProcess->setTimeout($this->timeout);

        return new MutantProcess($symfonyProcess, $mutant, $this->testFrameworkAdapter);
    }
}