<?php

declare(strict_types=1);

namespace Infection\Process\Runner;

use Infection\EventDispatcher\EventDispatcherInterface;
use Infection\Events\InitialTestCaseCompleted;
use Infection\Events\InitialTestSuiteFinished;
use Infection\Events\InitialTestSuiteStarted;
use Infection\Process\Builder\ProcessBuilder;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\TestFramework\PhpUnit\Coverage\CoverageXmlParser;
use Infection\Tests\TestFramework\PhpUnit\Config\AbstractXmlConfiguration;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Process\Process;

class InitialTestsRunner
{
    /**
     * @var Process
     */
    private $process;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var CodeCoverageData
     */
    private $coverageData;

    /**
     * InitialTestsRunner constructor.
     * @param ProcessBuilder $processBuilder
     * @param EventDispatcherInterface $eventDispatcher
     * @param CodeCoverageData $coverageData
     */
    public function __construct(ProcessBuilder $processBuilder, EventDispatcherInterface $eventDispatcher, CodeCoverageData $coverageData)
    {
        $this->processBuilder = $processBuilder;
        $this->eventDispatcher = $eventDispatcher;
        $this->coverageData = $coverageData;
    }

    public function run() : Result
    {
        $process = $this->processBuilder->build();

        $this->eventDispatcher->dispatch(new InitialTestSuiteStarted());

        $process->run(function ($type) use ($process) {
            if ($process::ERR === $type) {
                $process->stop();
            }

            $this->eventDispatcher->dispatch(new InitialTestCaseCompleted());

            // TODO parse PHPUnit output and add if (!ok) {stop()}
        });

        $this->eventDispatcher->dispatch(new InitialTestSuiteFinished());

        return new Result($process, $this->coverageData);
    }
}