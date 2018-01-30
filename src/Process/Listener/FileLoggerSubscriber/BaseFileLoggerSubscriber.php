<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

namespace Infection\Process\Listener\FileLoggerSubscriber;

use Infection\Config\InfectionConfig;
use Infection\Console\LogVerbosity;
use Infection\EventDispatcher\EventSubscriberInterface;
use Infection\Events\MutationTestingFinished;
use Infection\Filesystem\Filesystem;
use Infection\Mutant\MetricsCalculator;

class BaseFileLoggerSubscriber implements EventSubscriberInterface
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

        $logTypes = $this->filterLogTypes($logTypes);

        foreach ($logTypes as $logType => $directory) {
            $this->useLogger($logType);
        }
    }

    private function filterLogTypes(array $logTypes): array
    {
        $allowedFileTypes = [
            self::TEXT_FILE,
            self::DEBUG_FILE,
            self::SUMMARY_FILE,
        ];

        foreach ($logTypes as $key => $value) {
            if (!in_array($key, $allowedFileTypes)) {
                unset($logTypes[$key]);
            }
        }

        return $logTypes;
    }

    private function useLogger(string $logType)
    {
        switch ($logType) {
            case self::TEXT_FILE:
                (new TextFileLogger(
                    $this->infectionConfig,
                    $this->metricsCalculator,
                    $this->fs,
                    $this->isDebugMode
                ))->writeToFile();
                break;
            case self::SUMMARY_FILE:
                (new SummaryFileLogger(
                    $this->infectionConfig,
                    $this->metricsCalculator,
                    $this->fs,
                    $this->isDebugMode
                ))->writeToFile();
                break;
            case self::DEBUG_FILE:
                (new DebugFileLogger(
                    $this->infectionConfig,
                    $this->metricsCalculator,
                    $this->fs,
                    $this->isDebugMode
                ))->writeToFile();
                break;
        }
    }
}
