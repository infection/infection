<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Process\Builder;

use Infection\Configuration\Configuration;
use Infection\Console\OutputFormatter\DotFormatter;
use Infection\Console\OutputFormatter\OutputFormatter;
use Infection\Console\OutputFormatter\ProgressFormatter;
use Infection\Differ\DiffColorizer;
use Infection\EventDispatcher\EventDispatcherInterface;
use Infection\EventDispatcher\EventSubscriberInterface;
use Infection\Mutant\MetricsCalculator;
use Infection\Performance\Listener\PerformanceLoggerSubscriber;
use Infection\Performance\Memory\MemoryFormatter;
use Infection\Performance\Time\TimeFormatter;
use Infection\Performance\Time\Timer;
use Infection\Process\Listener\CiInitialTestsConsoleLoggerSubscriber;
use Infection\Process\Listener\CiMutantCreatingConsoleLoggerSubscriber;
use Infection\Process\Listener\CiMutationGeneratingConsoleLoggerSubscriber;
use Infection\Process\Listener\CleanUpAfterMutationTestingFinishedSubscriber;
use Infection\Process\Listener\InitialTestsConsoleLoggerSubscriber;
use Infection\Process\Listener\MutantCreatingConsoleLoggerSubscriber;
use Infection\Process\Listener\MutationGeneratingConsoleLoggerSubscriber;
use Infection\Process\Listener\MutationTestingConsoleLoggerSubscriber;
use Infection\Process\Listener\MutationTestingResultsLoggerSubscriber;
use Infection\TestFramework\TestFrameworkAdapter;
use InvalidArgumentException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class SubscriberBuilder
{
    /**
     * @var bool
     */
    private $showMutations;

    /**
     * @var string
     */
    private $logVerbosity;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var bool
     */
    private $onlyCovered;

    /**
     * @var bool
     */
    private $noProgress;

    /**
     * @var string
     */
    private $formatter;

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
     * @var Configuration
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

    /**
     * @var Timer
     */
    private $timer;

    /**
     * @var TimeFormatter
     */
    private $timeFormatter;

    /**
     * @var MemoryFormatter
     */
    private $memoryFormatter;

    public function __construct(
        bool $showMutations,
        string $logVerbosity,
        bool $debug,
        bool $onlyCovered,
        string $formatter,
        bool $noProgress,
        MetricsCalculator $metricsCalculator,
        EventDispatcherInterface $eventDispatcher,
        DiffColorizer $diffColorizer,
        Configuration $infectionConfig,
        Filesystem $fs,
        string $tmpDir,
        Timer $timer,
        TimeFormatter $timeFormatter,
        MemoryFormatter $memoryFormatter
    ) {
        $this->showMutations = $showMutations;
        $this->logVerbosity = $logVerbosity;
        $this->debug = $debug;
        $this->onlyCovered = $onlyCovered;
        $this->formatter = $formatter;
        $this->noProgress = $noProgress;
        $this->metricsCalculator = $metricsCalculator;
        $this->eventDispatcher = $eventDispatcher;
        $this->diffColorizer = $diffColorizer;
        $this->infectionConfig = $infectionConfig;
        $this->fs = $fs;
        $this->tmpDir = $tmpDir;
        $this->timer = $timer;
        $this->timeFormatter = $timeFormatter;
        $this->memoryFormatter = $memoryFormatter;
    }

    public function registerSubscribers(
        TestFrameworkAdapter $testFrameworkAdapter,
        OutputInterface $output
    ): void {
        foreach ($this->getSubscribers($testFrameworkAdapter, $output) as $subscriber) {
            $this->eventDispatcher->addSubscriber($subscriber);
        }
    }

    private function getSubscribers(
        TestFrameworkAdapter $testFrameworkAdapter,
        OutputInterface $output
    ): array {
        $subscribers = [
            $this->getInitialTestsConsoleLoggerSubscriber($testFrameworkAdapter, $output),
            $this->getMutantGeneratingConsoleLoggerSubscriber($output),
            $this->getMutantCreatingConsoleLoggerSubscriber($output),
            new MutationTestingConsoleLoggerSubscriber(
                $output,
                $this->getOutputFormatter($output),
                $this->metricsCalculator,
                $this->diffColorizer,
                $this->showMutations
            ),
            new MutationTestingResultsLoggerSubscriber(
                $output,
                $this->infectionConfig,
                $this->metricsCalculator,
                $this->fs,
                $this->logVerbosity,
                $this->debug,
                $this->onlyCovered
            ),
            new PerformanceLoggerSubscriber(
                $this->timer,
                $this->timeFormatter,
                $this->memoryFormatter,
                $output
            ),
        ];

        if (!$this->debug) {
            $subscribers[] = new CleanUpAfterMutationTestingFinishedSubscriber(
                $this->fs,
                $this->tmpDir
            );
        }

        return $subscribers;
    }

    private function getOutputFormatter(OutputInterface $output): OutputFormatter
    {
        if ($this->formatter === 'progress') {
            return new ProgressFormatter(new ProgressBar($output));
        }

        if ($this->formatter === 'dot') {
            return new DotFormatter($output);
        }

        throw new InvalidArgumentException('Incorrect formatter. Possible values: "dot", "progress"');
    }

    private function getMutantCreatingConsoleLoggerSubscriber(OutputInterface $output): EventSubscriberInterface
    {
        if ($this->shouldSkipProgressBars()) {
            return new CiMutantCreatingConsoleLoggerSubscriber($output);
        }

        return new MutantCreatingConsoleLoggerSubscriber($output);
    }

    private function getMutantGeneratingConsoleLoggerSubscriber(OutputInterface $output): EventSubscriberInterface
    {
        if ($this->shouldSkipProgressBars()) {
            return new CiMutationGeneratingConsoleLoggerSubscriber($output);
        }

        return new MutationGeneratingConsoleLoggerSubscriber($output);
    }

    private function getInitialTestsConsoleLoggerSubscriber(TestFrameworkAdapter $testFrameworkAdapter, OutputInterface $output): EventSubscriberInterface
    {
        if ($this->shouldSkipProgressBars()) {
            return new CiInitialTestsConsoleLoggerSubscriber($output, $testFrameworkAdapter);
        }

        return new InitialTestsConsoleLoggerSubscriber($output, $testFrameworkAdapter, $this->debug);
    }

    private function shouldSkipProgressBars(): bool
    {
        return $this->noProgress
            || getenv('CI') === 'true'
            || getenv('CONTINUOUS_INTEGRATION') === 'true';
    }
}
