<?php

declare(strict_types=1);

namespace Infection\Process\Builder;

use Infection\TestFramework\Adapter\TestFrameworkAdapter;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder as SymfonyProcessBuilder;

class ProcessBuilder
{
    /**
     * @var TestFrameworkAdapter
     */
    private $testFrameworkAdapter;

    public function __construct(TestFrameworkAdapter $testFrameworkAdapter)
    {

        $this->testFrameworkAdapter = $testFrameworkAdapter;
    }

    public function getProcess() : Process
    {
        $processBuilder = new SymfonyProcessBuilder([
            $this->testFrameworkAdapter->getExecutableCommandLine()
        ]);

        return $processBuilder->getProcess();
    }
}