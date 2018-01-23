<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

namespace Infection\Process\Listener\FileLogger;

use Infection\Config\InfectionConfig;
use Infection\Console\LogVerbosity;
use Infection\EventDispatcher\EventSubscriberInterface;
use Infection\Events\MutationTestingFinished;
use Infection\Filesystem\Filesystem;
use Infection\Mutant\MetricsCalculator;

class FileLoggerSubscriber implements EventSubscriberInterface
{
    const TEXT_FILE = 'text';
    const SUMMARY_FILE = 'summary';
    const DEBUG_FILE = 'debug';
    /**
     * @var InfectionConfig
     */
    private $infectionConfig;
    /**
     * @var MetricsCalculator
     */
    private $metricsCalculator;
    /**
     * @var Filesystem
     */
    private $fs;
    /**
     * @var bool
     */
    private $isDebugMode;

    public function __construct(
        InfectionConfig $infectionConfig,
        MetricsCalculator $metricsCalculator,
        Filesystem $fs,
        int $logVerbosity = LogVerbosity::DEBUG
    ) {
        $this->infectionConfig = $infectionConfig;
        $this->metricsCalculator = $metricsCalculator;
        $this->fs = $fs;
        $this->isDebugMode = ($logVerbosity === LogVerbosity::DEBUG);
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            MutationTestingFinished::class => [$this, 'onMutationTestingFinished'],
        ];
    }

    public function onMutationTestingFinished(MutationTestingFinished $event)
    {
        $logTypes = $this->infectionConfig->getLogsTypes();
        if (empty($logTypes)) {
            return;
        }

        foreach ($logTypes as $logType => $directory) {
            $this->useLogger($logType, $directory);
        }
    }

    private function useLogger(string $logType, string $directory)
    {
        switch ($logType) {
            case self::TEXT_FILE:
                (new TextFileLogger(
                    $directory,
                    $this->metricsCalculator,
                    $this->fs,
                    $this->isDebugMode
                ))->writeToFile();
                break;
            case self::SUMMARY_FILE:
                (new SummaryFileLogger(
                    $directory,
                    $this->metricsCalculator,
                    $this->fs,
                    $this->isDebugMode
                ))->writeToFile();
                break;
            case self::DEBUG_FILE:
                (new DebugFileLogger(
                    $directory,
                    $this->metricsCalculator,
                    $this->fs,
                    $this->isDebugMode
                ))->writeToFile();
                break;
        }
    }
}
