<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Event\Subscriber;

use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\TestFrameworkAdapter;
use _HumbugBox9658796bb9f0\Infection\Event\InitialTestCaseWasCompleted;
use _HumbugBox9658796bb9f0\Infection\Event\InitialTestSuiteWasFinished;
use _HumbugBox9658796bb9f0\Infection\Event\InitialTestSuiteWasStarted;
use InvalidArgumentException;
use const PHP_EOL;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper\ProgressBar;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
final class InitialTestsConsoleLoggerSubscriber implements EventSubscriber
{
    private ProgressBar $progressBar;
    public function __construct(private OutputInterface $output, private TestFrameworkAdapter $testFrameworkAdapter, private bool $debug)
    {
        $this->progressBar = new ProgressBar($this->output);
        $this->progressBar->setFormat('verbose');
    }
    public function onInitialTestSuiteWasStarted(InitialTestSuiteWasStarted $event) : void
    {
        try {
            $version = $this->testFrameworkAdapter->getVersion();
        } catch (InvalidArgumentException) {
            $version = 'unknown';
        }
        $this->output->writeln(['', 'Running initial test suite...', '', sprintf('%s version: %s', $this->testFrameworkAdapter->getName(), $version), '']);
        $this->progressBar->start();
    }
    public function onInitialTestSuiteWasFinished(InitialTestSuiteWasFinished $event) : void
    {
        $this->progressBar->finish();
        if ($this->debug) {
            $this->output->writeln(PHP_EOL . $event->getOutputText());
        }
    }
    public function onInitialTestCaseWasCompleted(InitialTestCaseWasCompleted $event) : void
    {
        $this->progressBar->advance();
    }
}
