<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Process\Listener;

use Infection\Config\InfectionConfig;
use Infection\Console\LogVerbosity;
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
            $logs[] = $this->getLogParts($this->metricsCalculator->getEscapedMutantProcesses(), 'Escaped');
            $logs[] = $this->getLogParts($this->metricsCalculator->getTimedOutProcesses(), 'Timeout');

            if ($this->isDebugMode) {
                $logs[] = $this->getLogParts($this->metricsCalculator->getKilledMutantProcesses(), 'Killed');
            }

            $logs[] = $this->getLogParts($this->metricsCalculator->getNotCoveredMutantProcesses(), 'Not covered');

            $this->fs->dumpFile(
                $logFilePath,
                implode(
                    array_merge(...$logs),
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
            $isShowFullFormat = $this->isDebugMode && $mutantProcess->getProcess()->isStarted();

            $logParts[] = '';
            $logParts[] = $this->getMutatorFirstLine($index, $mutantProcess);
            $logParts[] = $isShowFullFormat ? $mutantProcess->getProcess()->getCommandLine() : '';
            $logParts[] = $mutantProcess->getMutant()->getDiff();

            if ($isShowFullFormat) {
                $logParts[] = $mutantProcess->getProcess()->getOutput();
            }
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

    private function getMutatorFirstLine(int $index, MutantProcess $mutantProcess): string
    {
        return sprintf(
            '%d) %s:%d    [M] %s',
            $index + 1,
            $mutantProcess->getMutant()->getMutation()->getOriginalFilePath(),
            (int) $mutantProcess->getMutant()->getMutation()->getAttributes()['startLine'],
            $mutantProcess->getMutant()->getMutation()->getMutator()->getName()
        );
    }
}
