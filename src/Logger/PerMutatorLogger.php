<?php

/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
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

use Infection\Mutant\MetricsCalculator;

/**
 * @internal
 */
final class PerMutatorLogger extends FileLogger
{
    /**
     * @var MetricsCalculator[]
     */
    private $calculatorPerMutator = [];

    protected function getLogLines(): array
    {
        $this->setUpPerCalculatorMutator();

        $logs = [];

        $logs[] = '# Effects per Mutator' . PHP_EOL;

        $logs[] = '| Mutator | Mutations | Killed | Escaped | Errors | Timed Out | MSI | Covered MSI |';
        $logs[] = '| ------- | --------- | ------ | ------- |------- | --------- | --- | ----------- |';

        foreach ($this->calculatorPerMutator as $mutator => $calculator) {
            $logs[] = '| ' . $mutator . ' | ' .
                $calculator->getTotalMutantsCount() . ' | ' .
                $calculator->getKilledCount() . ' | ' .
                $calculator->getEscapedCount() . ' | ' .
                $calculator->getErrorCount() . ' | ' .
                $calculator->getTimedOutCount() . ' | ' .
                floor($calculator->getMutationScoreIndicator()) . '| ' .
                floor($calculator->getCoveredCodeMutationScoreIndicator()) . '|';
        }

        return $logs;
    }

    private function setUpPerCalculatorMutator(): void
    {
        $processes = $this->metricsCalculator->getAllMutantProcesses();

        $processPerMutator = [];

        foreach ($processes as $process) {
            $mutatorName = $process->getMutator()::getName();
            $processPerMutator[$mutatorName][] = $process;
        }

        foreach ($processPerMutator as $mutator => $processes) {
            $this->calculatorPerMutator[$mutator] = MetricsCalculator::createFromArray($processes);
        }

        ksort($this->calculatorPerMutator);
    }
}
