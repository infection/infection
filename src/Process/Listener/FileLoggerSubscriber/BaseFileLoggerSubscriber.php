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
        $logType = $this->infectionConfig->getLogsTypes();

        if ($logType === null) {
            return;
        }
        $logType = (array) $logType;

        if (isset($logType['text'])) {
            $this->logToTextFile();
        }

        if (isset($logType['summary'])) {
            $this->logSummary();
        }
    }

    private function logToTextFile()
    {
        (new TextFileLogger(
            $this->infectionConfig,
            $this->metricsCalculator,
            $this->fs,
            $this->isDebugMode
        ))
            ->writeToFile();
    }

    private function logSummary()
    {
        (new SummaryFileLogger(
            $this->infectionConfig,
            $this->metricsCalculator,
            $this->fs
        ))
            ->writeToFile();
    }
}
