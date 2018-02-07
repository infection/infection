<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Process\Runner;

use Infection\EventDispatcher\EventDispatcherInterface;
use Infection\Events\InitialTestCaseCompleted;
use Infection\Events\InitialTestSuiteFinished;
use Infection\Events\InitialTestSuiteStarted;
use Infection\Process\Builder\ProcessBuilder;
use Symfony\Component\Process\Process;

class InitialTestsRunner
{
    /**
     * @var ProcessBuilder
     */
    private $processBuilder;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * InitialTestsRunner constructor.
     *
     * @param ProcessBuilder $processBuilder
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(ProcessBuilder $processBuilder, EventDispatcherInterface $eventDispatcher)
    {
        $this->processBuilder = $processBuilder;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function run(string $testFrameworkExtraOptions, bool $skipCoverage): Process
    {
        $process = $this->processBuilder->getProcessForInitialTestRun($testFrameworkExtraOptions, $skipCoverage);

        var_dump($process->getCommandLine());

        $this->eventDispatcher->dispatch(new InitialTestSuiteStarted());

        $process->run(function ($type) use ($process) {
            if ($process::ERR === $type) {
                $process->stop();
            }

            $this->eventDispatcher->dispatch(new InitialTestCaseCompleted());
        });

        $this->eventDispatcher->dispatch(new InitialTestSuiteFinished());

        return $process;
    }
}
