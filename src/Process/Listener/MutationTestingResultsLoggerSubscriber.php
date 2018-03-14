<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

namespace Infection\Process\Listener;

use Infection\Config\InfectionConfig;
use Infection\Console\LogVerbosity;
use Infection\EventDispatcher\EventSubscriberInterface;
use Infection\Events\MutationTestingFinished;
use Infection\Http\BadgeApiClient;
use Infection\Logger\BadgeLogger;
use Infection\Logger\DebugFileLogger;
use Infection\Logger\ResultsLoggerTypes;
use Infection\Logger\SummaryFileLogger;
use Infection\Logger\TextFileLogger;
use Infection\Mutant\MetricsCalculator;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class MutationTestingResultsLoggerSubscriber implements EventSubscriberInterface
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

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(
        OutputInterface $output,
        InfectionConfig $infectionConfig,
        MetricsCalculator $metricsCalculator,
        Filesystem $fs,
        int $logVerbosity = LogVerbosity::DEBUG
    ) {
        $this->output = $output;
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

        foreach ($logTypes as $logType => $config) {
            $this->useLogger($logType, $config);
        }
    }

    private function filterLogTypes(array $logTypes): array
    {
        foreach (ResultsLoggerTypes::ALL as $key => $value) {
            if (!in_array($key, ResultsLoggerTypes::ALL, true)) {
                unset($logTypes[$key]);
            }
        }

        return $logTypes;
    }

    private function useLogger(string $logType, $config)
    {
        switch ($logType) {
            case ResultsLoggerTypes::TEXT_FILE:
                (new TextFileLogger(
                    $config,
                    $this->metricsCalculator,
                    $this->fs,
                    $this->isDebugMode
                ))->log();
                break;
            case ResultsLoggerTypes::SUMMARY_FILE:
                (new SummaryFileLogger(
                    $config,
                    $this->metricsCalculator,
                    $this->fs,
                    $this->isDebugMode
                ))->log();
                break;
            case ResultsLoggerTypes::DEBUG_FILE:
                (new DebugFileLogger(
                    $config,
                    $this->metricsCalculator,
                    $this->fs,
                    $this->isDebugMode
                ))->log();
                break;
            case ResultsLoggerTypes::BADGE:
                (new BadgeLogger(
                    $this->output,
                    new BadgeApiClient($this->output),
                    $this->metricsCalculator,
                    $config
                ))->log();
                break;
        }
    }
}
