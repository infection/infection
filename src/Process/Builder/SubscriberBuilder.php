<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Process\Builder;

use Infection\Config\InfectionConfig;
use Infection\Console\OutputFormatter\DotFormatter;
use Infection\Console\OutputFormatter\OutputFormatter;
use Infection\Console\OutputFormatter\ProgressFormatter;
use Infection\Differ\DiffColorizer;
use Infection\EventDispatcher\EventDispatcherInterface;
use Infection\Mutant\MetricsCalculator;
use Infection\Process\Listener\CleanUpAfterMutationTestingFinishedSubscriber;
use Infection\Process\Listener\InitialTestsConsoleLoggerSubscriber;
use Infection\Process\Listener\MutantCreatingConsoleLoggerSubscriber;
use Infection\Process\Listener\MutationGeneratingConsoleLoggerSubscriber;
use Infection\Process\Listener\MutationTestingConsoleLoggerSubscriber;
use Infection\Process\Listener\MutationTestingResultsLoggerSubscriber;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class SubscriberBuilder
{
    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var MetricsCalculator
     */
    private $metricsCalculator;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var DiffColorizer
     */
    private $diffColorizer;

    /**
     * @var InfectionConfig
     */
    private $infectionConfig;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var string
     */
    private $tmpDir;

    public function __construct(
        InputInterface $input,
        MetricsCalculator $metricsCalculator,
        EventDispatcherInterface $eventDispatcher,
        DiffColorizer $diffColorizer,
        InfectionConfig $infectionConfig,
        Filesystem $fs,
        string $tmpDir
    ) {
        $this->input = $input;
        $this->metricsCalculator = $metricsCalculator;
        $this->eventDispatcher = $eventDispatcher;
        $this->diffColorizer = $diffColorizer;
        $this->infectionConfig = $infectionConfig;
        $this->fs = $fs;
        $this->tmpDir = $tmpDir;
    }

    public function registerSubscribers(
        AbstractTestFrameworkAdapter $testFrameworkAdapter,
        OutputInterface $output
    ): void {
        foreach ($this->getSubscribers($testFrameworkAdapter, $output) as $subscriber) {
            $this->eventDispatcher->addSubscriber($subscriber);
        }
    }

    private function getSubscribers(
        AbstractTestFrameworkAdapter $testFrameworkAdapter,
        OutputInterface $output
    ): array {
        $subscribers = [
            new InitialTestsConsoleLoggerSubscriber($output, $testFrameworkAdapter),
            new MutationGeneratingConsoleLoggerSubscriber($output),
            new MutantCreatingConsoleLoggerSubscriber($output),
            new MutationTestingConsoleLoggerSubscriber(
                $output,
                $this->getOutputFormatter($output),
                $this->metricsCalculator,
                $this->diffColorizer,
                $this->input->getOption('show-mutations')
            ),
            new MutationTestingResultsLoggerSubscriber(
                $output,
                $this->infectionConfig,
                $this->metricsCalculator,
                $this->fs,
                $this->input->getOption('log-verbosity'),
                (bool) $this->input->getOption('debug')
            ),
        ];

        if (!$this->input->getOption('debug')) {
            $subscribers[] = new CleanUpAfterMutationTestingFinishedSubscriber(
                $this->fs,
                $this->tmpDir
            );
        }

        return $subscribers;
    }

    private function getOutputFormatter(OutputInterface $output): OutputFormatter
    {
        if ($this->input->getOption('formatter') === 'progress') {
            return new ProgressFormatter(new ProgressBar($output));
        }

        if ($this->input->getOption('formatter') === 'dot') {
            return new DotFormatter($output);
        }

        throw new \InvalidArgumentException('Incorrect formatter. Possible values: "dot", "progress"');
    }
}
