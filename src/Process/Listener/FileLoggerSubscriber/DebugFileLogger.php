<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

namespace Infection\Process\Listener\FileLoggerSubscriber;

use Infection\Process\MutantProcess;

class DebugFileLogger extends FileLogger
{
    public function writeToFile()
    {
        $logFilePath = $this->infectionConfig->getLogPathInfoFor('debug');

        $logs = [];

        $logs[] = 'Total: ' . $this->metricsCalculator->getTotalMutantsCount();
        $logs[] = $this->convertProcess(
            $this->metricsCalculator->getKilledMutantProcesses(),
            'Killed'
        );
        $logs[] = $this->convertProcess(
            $this->metricsCalculator->getErrorProcesses(),
            'Errors'
        );
        $logs[] = $this->convertProcess(
            $this->metricsCalculator->getEscapedMutantProcesses(),
            'Escaped'
        );
        $logs[] = $this->convertProcess(
            $this->metricsCalculator->getTimedOutProcesses(),
            'Timed Out'
        );
        $logs[] = $this->convertProcess(
            $this->metricsCalculator->getNotCoveredMutantProcesses(),
            'Not Covered'
        );

        $this->write($logs, $logFilePath);
    }

    /**
     * @param MutantProcess[] $processes
     * @param string $headlinePrefix
     *
     * @return string
     */
    private function convertProcess(array $processes, string $headlinePrefix): string
    {
        $logParts = $this->getHeadlineParts($headlinePrefix);

        foreach ($processes as $index => $mutantProcess) {
            $logParts[] = '';
            $mutation = $mutantProcess->getMutant()->getMutation();
            $logParts[] = 'Mutator: ' . $mutation->getMutator()->getName();
            $logParts[] = 'Line ' . $mutation->getAttributes()['startLine'];
        }

        return implode($logParts, "\n") . "\n";
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
}
