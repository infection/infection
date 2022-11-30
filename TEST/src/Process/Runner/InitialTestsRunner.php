<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Process\Runner;

use _HumbugBox9658796bb9f0\Infection\Event\EventDispatcher\EventDispatcher;
use _HumbugBox9658796bb9f0\Infection\Event\InitialTestCaseWasCompleted;
use _HumbugBox9658796bb9f0\Infection\Event\InitialTestSuiteWasFinished;
use _HumbugBox9658796bb9f0\Infection\Event\InitialTestSuiteWasStarted;
use _HumbugBox9658796bb9f0\Infection\Process\Factory\InitialTestsRunProcessFactory;
use _HumbugBox9658796bb9f0\Symfony\Component\Process\Process;
class InitialTestsRunner
{
    public function __construct(private InitialTestsRunProcessFactory $processBuilder, private EventDispatcher $eventDispatcher)
    {
    }
    public function run(string $testFrameworkExtraOptions, array $phpExtraOptions, bool $skipCoverage) : Process
    {
        $process = $this->processBuilder->createProcess($testFrameworkExtraOptions, $phpExtraOptions, $skipCoverage);
        $this->eventDispatcher->dispatch(new InitialTestSuiteWasStarted());
        $process->run(function (string $type) use($process) : void {
            if ($process::ERR === $type) {
                $process->stop();
            }
            $this->eventDispatcher->dispatch(new InitialTestCaseWasCompleted());
        });
        $this->eventDispatcher->dispatch(new InitialTestSuiteWasFinished($process->getOutput()));
        return $process;
    }
}
