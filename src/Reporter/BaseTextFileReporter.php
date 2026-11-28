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

use function array_map;
use function array_merge;
use function array_push;
use function count;
use function explode;
use function implode;
use Infection\Framework\Str;
use Infection\Metrics\ResultsCollector;
use Infection\Mutant\MutantExecutionResult;
use Infection\Process\MutantProcess;
use const PHP_EOL;
use function Pipeline\take;
use function sprintf;

/**
 * @internal
 */
abstract readonly class BaseTextFileReporter implements LineMutationTestingResultsReporter
{
    public function __construct(
        private ResultsCollector $resultsCollector,
        private bool $debugVerbosity,
        private bool $onlyCoveredMode,
        private bool $debugMode,
    ) {
    }

    public function getLines(): array
    {
        $separateSections = false;

        $logs = [];

        $notes = [];

        if (!$this->debugVerbosity) {
            $notes[] = 'Note: Pass `--log-verbosity=all` to log information about killed and errored mutants.';
        }

        if (!$this->debugMode) {
            $notes[] = 'Note: Pass `--debug` to log test-framework output.';
        }

        if ($notes !== []) {
            foreach ($notes as $note) {
                $logs[] = $note;
            }
            $logs[] = '';
        }

        $logs[] = $this->getResultsLine(
            $this->resultsCollector->getEscapedExecutionResults(),
            'Escaped',
            $separateSections,
        );

        $logs[] = $this->getResultsLine(
            $this->resultsCollector->getTimedOutExecutionResults(),
            'Timed Out',
            $separateSections,
        );

        $logs[] = $this->getResultsLine(
            $this->resultsCollector->getSkippedExecutionResults(),
            'Skipped',
            $separateSections,
        );

        if ($this->debugVerbosity) {
            $logs[] = $this->getResultsLine(
                $this->resultsCollector->getKilledExecutionResults(),
                'Killed by Test Framework',
                $separateSections,
            );

            $logs[] = $this->getResultsLine(
                $this->resultsCollector->getKilledByStaticAnalysisExecutionResults(),
                'Killed by Static Analysis',
                $separateSections,
            );

            $logs[] = $this->getResultsLine(
                $this->resultsCollector->getErrorExecutionResults(),
                'Errors',
                $separateSections,
            );

            $logs[] = $this->getResultsLine(
                $this->resultsCollector->getSyntaxErrorExecutionResults(),
                'Syntax Errors',
                $separateSections,
            );
        }

        if (!$this->onlyCoveredMode) {
            $logs[] = $this->getResultsLine(
                $this->resultsCollector->getNotCoveredExecutionResults(),
                'Not Covered',
                $separateSections,
            );
        }

        if ($separateSections) {
            $logs[] = '';
        }

        return $logs;
    }

    /**
     * @param MutantExecutionResult[] $executionResults
     */
    protected function getResultsLine(
        array $executionResults,
        string $headlinePrefix,
        bool &$separateSections,
    ): string {
        $lines = [];

        if ($separateSections) {
            $lines[] = '';
            $lines[] = '';
        }

        $lines[] = $this->getHeadlineLines($headlinePrefix);

        $separateSections = false;

        foreach ($executionResults as $index => $executionResult) {
            if ($separateSections) {
                $lines[] = '';
                $lines[] = '';
            }

            $lines[] = self::getMutatorLine($index, $executionResult);
            $lines[] = '';
            $lines[] = Str::cleanForDisplay($executionResult->getMutantDiff());

            array_push($lines, ...$this->getEvaluationLines($executionResult));

            $separateSections = true;
        }

        return implode(PHP_EOL, $lines);
    }

    abstract protected function getHeadlineLines(string $headlinePrefix): string;

    /**
     * @return list<string>
     */
    private function getEvaluationLines(MutantExecutionResult $result): array
    {
        $history = $result->getEvaluationHistory();

        if ($history === []) {
            return $this->getProcessLines($result->getProcessCommandLine(), $result->getProcessOutput());
        }

        $showEvaluator = count($history) > 1;
        $lines = take($history)
            ->cast(fn (MutantProcess $attempt): array => $this->getProcessLines(
                $attempt->getProcess()->getCommandLine(),
                $attempt->getResultOutput(),
                $showEvaluator
                    ? $attempt->getTestFrameworkName()
                    : null,
            ))
            ->toList()
        ;

        return array_merge(...$lines);
    }

    /**
     * @return list<string>
     */
    private function getProcessLines(?string $commandLine, string $output, ?string $evaluator = null): array
    {
        if (!$this->debugMode && !$this->debugVerbosity) {
            return [];
        }

        $lines = [];

        if ($evaluator !== null) {
            $lines[] = Str::cleanForDisplay($evaluator) . ':';
        }

        if ($this->debugMode && $commandLine !== null) {
            $lines[] = '$ ' . $commandLine;
        }

        if ($this->debugVerbosity) {
            $lines[] = self::getProcessOutputLine($output);
        }

        return $lines === []
            ? []
            : ['', ...$lines];
    }

    private static function getMutatorLine(int $index, MutantExecutionResult $mutantProcess): string
    {
        return sprintf(
            '%d) %s:%d    [M] %s [ID] %s',
            $index + 1,
            $mutantProcess->getOriginalFilePath(),
            $mutantProcess->getOriginalStartingLine(),
            $mutantProcess->getMutatorName(),
            $mutantProcess->getMutantHash(),
        );
    }

    private static function getProcessOutputLine(string $value): string
    {
        return implode(
            PHP_EOL,
            array_map(
                static fn (string $line): string => '  ' . $line,
                explode("\n", Str::cleanForDisplay($value)),
            ),
        );
    }
}
