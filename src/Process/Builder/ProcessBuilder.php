<?php

declare(strict_types=1);

namespace Infection\Process\Builder;

use Infection\Mutant\Mutant;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Infection\TestFramework\Config\Builder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder as SymfonyProcessBuilder;

class ProcessBuilder
{
    /**
     * @var AbstractTestFrameworkAdapter
     */
    private $testFrameworkAdapter;

    public function __construct(AbstractTestFrameworkAdapter $testFrameworkAdapter)
    {
        $this->testFrameworkAdapter = $testFrameworkAdapter;
    }

    public function build() : Process
    {
        $configPath = $this->testFrameworkAdapter->buildConfigFile();

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
     * @param Mutant $mutant
     * @return Process
     */
    public function getProcessForMutant(Mutant $mutant) : Process
    {
        $configPath = $this->testFrameworkAdapter->buildConfigFile($mutant);

        return new Process(
            $this->testFrameworkAdapter->getExecutableCommandLine($configPath),
            null,
            array_replace($_ENV, $_SERVER)
        );
    }

    /**
     * @return AbstractTestFrameworkAdapter
     */
    public function getTestFrameworkAdapter(): AbstractTestFrameworkAdapter
    {
        return $this->testFrameworkAdapter;
    }
}