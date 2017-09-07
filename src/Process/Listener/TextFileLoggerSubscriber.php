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
use Infection\Filesystem\Filesystem;
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

    /**
     * @var Filesystem
     */
    private $fs;

    public function __construct(InfectionConfig $infectionConfig, MetricsCalculator $metricsCalculator, Filesystem $fs)
    {
        $this->infectionConfig = $infectionConfig;
        $this->metricsCalculator = $metricsCalculator;
        $this->fs = $fs;
    }

    public function getSubscribedEvents()
    {
        return [
            MutationTestingFinished::class => [$this, 'onMutationTestingFinished'],
        ];
    }

    public function onMutationTestingFinished(MutationTestingFinished $event)
    {
        $logFilePath = $this->infectionConfig->getTextFileLogPath();

        if ($logFilePath) {
            $this->fs->mkdir(\dirname($logFilePath));

            $escapedParts = $this->getLogParts($this->metricsCalculator->getEscapedMutantProcesses(), 'Escaped');

            $timedOutParts = $this->getLogParts($this->metricsCalculator->getTimedOutProcesses(), 'Timeout');

            $killedParts = $this->getLogParts($this->metricsCalculator->getKilledMutantProcesses(), 'Killed');

            $notCoveredParts = $this->getNotCoveredLogParts($this->metricsCalculator->getNotCoveredMutantProcesses(), 'Not covered');

            file_put_contents(
                $logFilePath,
                implode(
                    array_merge($escapedParts, $timedOutParts, $killedParts, $notCoveredParts),
                   "\n"
                )
            );
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
        $logParts = $this->getHeadlineParts($headlinePrefix);

        foreach ($processes as $index => $mutantProcess) {
            $logParts[] = '';
            $logParts[] = $this->getMutatorPart($index, $mutantProcess);
            $logParts[] = $mutantProcess->getMutant()->getMutation()->getOriginalFilePath();
            $logParts[] = $mutantProcess->getProcess()->getCommandLine();
            $logParts[] = $mutantProcess->getMutant()->getDiff();
            $logParts[] = $mutantProcess->getProcess()->getOutput();
        }

        return $logParts;
    }

    private function getNotCoveredLogParts(array $processes, string $headlinePrefix): array
    {
        $logParts = $this->getHeadlineParts($headlinePrefix);

        foreach ($processes as $index => $mutantProcess) {
            $logParts[] = '';
            $logParts[] = $this->getMutatorPart($index, $mutantProcess);
            $logParts[] = sprintf(
                '%s:%s',
                $mutantProcess->getMutant()->getMutation()->getOriginalFilePath(),
                $mutantProcess->getMutant()->getMutation()->getAttributes()['startLine']
            );
            $logParts[] = '';
            $logParts[] = $mutantProcess->getMutant()->getDiff();
        }

        return $logParts;
    }

    private function getHeadlineParts(string $headlinePrefix): array
    {
        $headline = sprintf('%s mutants:', $headlinePrefix);

        return [
            $headline,
            str_repeat('=', strlen($headline)),
            '',
        ];
    }

    private function getMutatorPart(int $index, MutantProcess $mutantProcess): string
    {
        return sprintf(
            '%d) %s',
            $index + 1,
            get_class($mutantProcess->getMutant()->getMutation()->getMutator())
        );
    }
}
