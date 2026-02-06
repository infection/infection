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

namespace Infection\Reporter;

use function count;
use Infection\Differ\DiffColorizer;
use Infection\Metrics\ResultsCollector;
use Infection\Mutant\MutantExecutionResult;
use LogicException;
use function sprintf;
use function str_repeat;
use function strlen;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class ShowMutationsReporter implements Reporter
{
    private ?int $numberOfMutationsBudget;

    public function __construct(
        private OutputInterface $output,
        private ResultsCollector $resultsCollector,
        private DiffColorizer $diffColorizer,
        private ?int $numberOfShownMutations,
        private bool $withUncovered,
        private bool $withTimeouts,
    ) {
        $this->numberOfMutationsBudget = $this->numberOfShownMutations;
    }

    // Was previously MutationTestingConsoleLoggerSubscriber::onMutationTestingWasFinished()
    // ::showMutations() calls
    public function report(): void
    {
        if ($this->numberOfMutationsBudget !== 0) {
            $this->showMutations($this->resultsCollector->getEscapedExecutionResults(), 'Escaped');

            if ($this->withUncovered) {
                $this->showMutations($this->resultsCollector->getNotCoveredExecutionResults(), 'Not covered');
            }

            if ($this->withTimeouts) {
                $this->showMutations($this->resultsCollector->getTimedOutExecutionResults(), 'Timed out');
            }
        }
    }

    /**
     * @param MutantExecutionResult[] $executionResults
     */
    private function showMutations(array $executionResults, string $headlinePrefix): void
    {
        if ($executionResults === [] || $this->numberOfMutationsBudget === 0) {
            return;
        }

        $headline = sprintf('%s mutants:', $headlinePrefix);

        $this->output->writeln([
            '',
            $headline,
            str_repeat('=', strlen($headline)),
            '',
        ]);

        $shortened = false;

        foreach ($executionResults as $index => $executionResult) {
            if ($this->numberOfMutationsBudget === 0) {
                $shortened = true;

                break;
            }

            $this->output->writeln([
                '',
                sprintf(
                    '%d) %s:%d    [M] %s [ID] %s',
                    $index + 1,
                    $executionResult->getOriginalFilePath(),
                    $executionResult->getOriginalStartingLine(),
                    $executionResult->getMutatorName(),
                    $executionResult->getMutantHash(),
                ),
            ]);

            $this->output->writeln($this->diffColorizer->colorize($executionResult->getMutantDiff()));

            if ($this->numberOfMutationsBudget !== null) {
                --$this->numberOfMutationsBudget;
            }
        }

        if ($shortened) {
            if (!isset($index)) {
                throw new LogicException('$index should be set when $shortened is true');
            }

            $this->output->writeln([
                '',
                sprintf(
                    '... and %d more mutants were omitted. Use "--show-mutations=max" to see all of them.',
                    count($executionResults) - $index,
                ),
            ]);
        }
    }
}
