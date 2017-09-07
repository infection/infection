<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Process\Listener;

use Infection\EventDispatcher\EventSubscriberInterface;
use Infection\Events\InitialTestCaseCompleted;
use Infection\Events\InitialTestSuiteFinished;
use Infection\Events\InitialTestSuiteStarted;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class InitialTestsConsoleLoggerSubscriber implements EventSubscriberInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ProgressBar
     */
    private $progressBar;

    /**
     * @var AbstractTestFrameworkAdapter
     */
    private $testFrameworkAdapter;

    public function __construct(OutputInterface $output, ProgressBar $progressBar, AbstractTestFrameworkAdapter $testFrameworkAdapter)
    {
        $this->output = $output;
        $this->progressBar = $progressBar;
        $this->testFrameworkAdapter = $testFrameworkAdapter;
    }

    public function getSubscribedEvents()
    {
        return [
            InitialTestSuiteStarted::class => [$this, 'onInitialTestSuiteStarted'],
            InitialTestSuiteFinished::class => [$this, 'onInitialTestSuiteFinished'],
            InitialTestCaseCompleted::class => [$this, 'onInitialTestCaseCompleted'],
        ];
    }

    public function onInitialTestSuiteStarted(InitialTestSuiteStarted $event)
    {
        $testFramework = $this->testFrameworkAdapter;

        $this->output->writeln([
            'Running initial test suite...',
            '',
            sprintf(
                '%s version: %s',
                ucfirst($testFramework::NAME),
                $testFramework->getVersion()
            ),
            ''
        ]);
        $this->progressBar->start();
    }

    public function onInitialTestSuiteFinished(InitialTestSuiteFinished $event)
    {
        $this->progressBar->finish();
    }

    public function onInitialTestCaseCompleted(InitialTestCaseCompleted $event)
    {
        $this->progressBar->advance();
    }
}
