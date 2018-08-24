<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Logger;

use Infection\Process\MutantProcessInterface;

/**
 * @internal
 */
final class DebugFileLogger extends FileLogger
{
    protected function getLogLines(): array
    {
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

        return $logs;
    }

    /**
     * @param MutantProcessInterface[] $processes
     * @param string $headlinePrefix
     *
     * @return string
     */
    private function convertProcess(array $processes, string $headlinePrefix): string
    {
        $logParts = $this->getHeadlineParts($headlinePrefix);
        $this->sortProcesses($processes);

        foreach ($processes as $mutantProcess) {
            $logParts[] = '';
            $logParts[] = 'Mutator: ' . $mutantProcess->getMutator()::getName();
            $logParts[] = 'Line ' . $mutantProcess->getOriginalStartingLine();
        }

        return implode("\n", $logParts) . "\n";
    }

    private function getHeadlineParts(string $headlinePrefix): array
    {
        $headline = sprintf('%s mutants:', $headlinePrefix);

        return [
            $headline,
            str_repeat('=', \strlen($headline)),
            '',
        ];
    }
}
