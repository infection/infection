<?php

declare(strict_types=1);

namespace Infection\Process\Builder;

use Infection\TestFramework\AbstractTestFrameworkAdapter;
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

    public function getProcess() : Process
    {

        return new Process(
            $this->testFrameworkAdapter->getExecutableCommandLine(),
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
}