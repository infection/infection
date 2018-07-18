<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
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

/**
 * @internal
 */
final class InitialTestsConsoleLoggerSubscriber implements EventSubscriberInterface
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

    public function __construct(OutputInterface $output, AbstractTestFrameworkAdapter $testFrameworkAdapter)
    {
        $this->output = $output;
        $this->testFrameworkAdapter = $testFrameworkAdapter;

        $this->progressBar = new ProgressBar($this->output);
        $this->progressBar->setFormat('verbose');
    }

    public function getSubscribedEvents(): array
    {
        return [
            InitialTestSuiteStarted::class => [$this, 'onInitialTestSuiteStarted'],
            InitialTestSuiteFinished::class => [$this, 'onInitialTestSuiteFinished'],
            InitialTestCaseCompleted::class => [$this, 'onInitialTestCaseCompleted'],
        ];
    }

    public function onInitialTestSuiteStarted(InitialTestSuiteStarted $event): void
    {
        try {
            $version = $this->testFrameworkAdapter->getVersion();
        } catch (\InvalidArgumentException $e) {
            $version = 'unknown';
        }

        $this->output->writeln([
            'Running initial test suite...',
            '',
            sprintf(
                '%s version: %s',
                $this->testFrameworkAdapter->getName(),
                $version
            ),
            '',
        ]);
        $this->progressBar->start();
    }

    public function onInitialTestSuiteFinished(InitialTestSuiteFinished $event): void
    {
        $this->progressBar->finish();
    }

    public function onInitialTestCaseCompleted(InitialTestCaseCompleted $event): void
    {
        $this->progressBar->advance();
    }
}
