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
final class TextFileLogger extends FileLogger
{
    protected function getLogLines(): array
    {
        $logs[] = $this->getLogParts($this->metricsCalculator->getEscapedMutantProcesses(), 'Escaped');
        $logs[] = $this->getLogParts($this->metricsCalculator->getTimedOutProcesses(), 'Timed Out');

        if ($this->isDebugVerbosity) {
            $logs[] = $this->getLogParts($this->metricsCalculator->getKilledMutantProcesses(), 'Killed');
            $logs[] = $this->getLogParts($this->metricsCalculator->getErrorProcesses(), 'Errors');
        }

        $logs[] = $this->getLogParts($this->metricsCalculator->getNotCoveredMutantProcesses(), 'Not Covered');

        return $logs;
    }

    /**
     * @param MutantProcessInterface[] $processes
     * @param string $headlinePrefix
     *
     * @return string
     */
    private function getLogParts(array $processes, string $headlinePrefix): string
    {
        $logParts = $this->getHeadlineParts($headlinePrefix);
        $this->sortProcesses($processes);

        foreach ($processes as $index => $mutantProcess) {
            $isShowFullFormat = $this->isDebugVerbosity && $mutantProcess->getProcess()->isStarted();

            $logParts[] = '';
            $logParts[] = $this->getMutatorFirstLine($index, $mutantProcess);

            $logParts[] = $this->isDebugMode ? $mutantProcess->getProcess()->getCommandLine() : '';

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
            str_repeat('=', \strlen($headline)),
            '',
        ];
    }

    private function getMutatorFirstLine(int $index, MutantProcessInterface $mutantProcess): string
    {
        return sprintf(
            '%d) %s:%d    [M] %s',
            $index + 1,
            $mutantProcess->getOriginalFilePath(),
            $mutantProcess->getOriginalStartingLine(),
            $mutantProcess->getMutator()::getName()
        );
    }
}
