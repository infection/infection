<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Process\Listener;

use Infection\Config\InfectionConfig;
use Infection\EventDispatcher\EventSubscriberInterface;
use Infection\Events\MutationTestingFinished;
use Infection\Mutant\MetricsCalculator;
use Infection\Process\MutantProcess;

class TextFileLoggerSubscriber implements EventSubscriberInterface
{
    /**
     * @var InfectionConfig
     */
    private $infectionConfig;

    /**
     * @var MetricsCalculator
     */
    private $metricsCalculator;

    public function __construct(InfectionConfig $infectionConfig, MetricsCalculator $metricsCalculator)
    {
        $this->infectionConfig = $infectionConfig;
        $this->metricsCalculator = $metricsCalculator;
    }

    public function getSubscribedEvents()
    {
        return [
            MutationTestingFinished::class => [$this, 'onMutationTestingFinished'],
        ];
    }

    public function onMutationTestingFinished(MutationTestingFinished $event)
    {
        $textFileLogPath = $this->infectionConfig->getTextFileLogPath();

        if ($textFileLogPath) {
            $logParts = [];

            $logParts = array_merge(
                $logParts,
                $this->getLogParts($this->metricsCalculator->getEscapedMutantProcesses(), 'Escaped')
            );

            $logParts = array_merge(
                $logParts,
                $this->getLogParts($this->metricsCalculator->getTimedOutProcesses(), 'Timeout')
            );

            $logParts = array_merge(
                $logParts,
                $this->getLogParts($this->metricsCalculator->getKilledMutantProcesses(), 'Killed')
            );

            $logParts = array_merge(
                $logParts,
                $this->getLogParts($this->metricsCalculator->getNotCoveredMutantProcesses(), 'Uncovered')
            );

            file_put_contents($textFileLogPath, implode($logParts, "\n"));
        }
    }

    /**
     * @param MutantProcess[] $processes
     * @param string $headlinePrefix
     *
     * @return array
     */
    private function getLogParts(array $processes, string $headlinePrefix): array
    {
        $logParts = [sprintf('%s mutants:', $headlinePrefix), ''];

        foreach ($processes as $index => $mutantProcess) {
            $mutant = $mutantProcess->getMutant();
            $process = $mutantProcess->getProcess();

            $logParts[] = '';
            $logParts[] = sprintf('%d) %s', $index + 1, get_class($mutant->getMutation()->getMutator()));
            $logParts[] = $mutant->getMutation()->getOriginalFilePath();
            $logParts[] = $process->getCommandLine();
            $logParts[] = $mutant->getDiff();
            $logParts[] = $mutant->isCoveredByTest() ? $process->getOutput() : '';
        }

        return $logParts;
    }
}
