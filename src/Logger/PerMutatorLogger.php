<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
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

        $logs[] = "# Effects per Mutator\n";

        $logs[] = '| Mutator | Mutations | Killed | Escaped | Errors | Timed Out | MSI | Covered MSI |';
        $logs[] = '| ------- | --------- | ------ | ------- |------- | --------- | --- | ----------- |';

        foreach ($this->calculatorPerMutator as $mutator => $calculator) {
            $logs[] = '| ' . $mutator . ' | ' .
                $calculator->getTotalMutantsCount() . ' | ' .
                $calculator->getKilledCount() . ' | ' .
                $calculator->getEscapedCount() . ' | ' .
                $calculator->getErrorCount() . ' | ' .
                $calculator->getTimedOutCount() . ' | ' .
                $calculator->getMutationScoreIndicator() . '| ' .
                $calculator->getCoveredCodeMutationScoreIndicator() . '|';
        }

        return $logs;
    }

    private function setUpPerCalculatorMutator()
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
