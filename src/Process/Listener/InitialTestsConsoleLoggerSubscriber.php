<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types = 1);

namespace Infection\Process\Listener;

use Infection\EventDispatcher\EventSubscriberInterface;
use Infection\Events\InitialTestCaseCompleted;
use Infection\Events\InitialTestSuiteFinished;
use Infection\Events\InitialTestSuiteStarted;
use Infection\Events\MutationTestingFinished;
use Infection\Events\MutationTestingStarted;
use Infection\Events\MutantProcessFinished;
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

    public function __construct(OutputInterface $output, ProgressBar $progressBar)
    {
        $this->output = $output;
        $this->progressBar = $progressBar;
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