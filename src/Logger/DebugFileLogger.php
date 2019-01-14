<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2019, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
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

        return implode(PHP_EOL, $logParts) . PHP_EOL;
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
