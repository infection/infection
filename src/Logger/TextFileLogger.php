<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Logger;

use Infection\Process\MutantProcess;
use Infection\Process\MutantProcessInterface;

final class TextFileLogger extends FileLogger
{
    protected function getLogLines(): array
    {
        $logs[] = $this->getLogParts($this->metricsCalculator->getEscapedMutantProcesses(), 'Escaped');
        $logs[] = $this->getLogParts($this->metricsCalculator->getTimedOutProcesses(), 'Timeout');

        if ($this->isDebugMode) {
            $logs[] = $this->getLogParts($this->metricsCalculator->getKilledMutantProcesses(), 'Killed');
            $logs[] = $this->getLogParts($this->metricsCalculator->getErrorProcesses(), 'Errors');
        }

        $logs[] = $this->getLogParts($this->metricsCalculator->getNotCoveredMutantProcesses(), 'Not covered');

        return $logs;
    }

    /**
     * @param MutantProcess[] $processes
     * @param string $headlinePrefix
     *
     * @return string
     */
    private function getLogParts(array $processes, string $headlinePrefix): string
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

        return implode($logParts, "\n");
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

    private function getMutatorFirstLine(int $index, MutantProcessInterface $mutantProcess): string
    {
        $mutation = $mutantProcess->getMutant()->getMutation();

        return sprintf(
            '%d) %s:%d    [M] %s',
            $index + 1,
            $mutation->getOriginalFilePath(),
            (int) $mutation->getAttributes()['startLine'],
            $mutation->getMutator()::getName()
        );
    }
}
