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

use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\ResultsCollector;
use Infection\Mutant\MutantExecutionResult;
use Infection\Str;
use function json_encode;
use const JSON_THROW_ON_ERROR;
use function mb_convert_encoding;

/**
 * @internal
 */
final class JsonLogger implements LineMutationTestingResultsLogger
{
    private MetricsCalculator $metricsCalculator;
    private ResultsCollector $resultsCollector;
    private bool $onlyCoveredMode;

    public function __construct(
        MetricsCalculator $metricsCalculator,
        ResultsCollector $resultsCollector,
        bool $onlyCoveredMode
    ) {
        $this->metricsCalculator = $metricsCalculator;
        $this->resultsCollector = $resultsCollector;
        $this->onlyCoveredMode = $onlyCoveredMode;
    }

    /**
     * @return array{0: string}
     */
    public function getLogLines(): array
    {
        $data = [
            'stats' => [
                'totalMutantsCount' => $this->metricsCalculator->getTotalMutantsCount(),
                'killedCount' => $this->metricsCalculator->getKilledCount(),
                'notCoveredCount' => $this->metricsCalculator->getNotTestedCount(),
                'escapedCount' => $this->metricsCalculator->getEscapedCount(),
                'errorCount' => $this->metricsCalculator->getErrorCount(),
                'syntaxErrorCount' => $this->metricsCalculator->getSyntaxErrorCount(),
                'skippedCount' => $this->metricsCalculator->getSkippedCount(),
                'timeOutCount' => $this->metricsCalculator->getTimedOutCount(),
                'msi' => $this->metricsCalculator->getMutationScoreIndicator(),
                'mutationCodeCoverage' => $this->metricsCalculator->getCoverageRate(),
                'coveredCodeMsi' => $this->metricsCalculator->getCoveredCodeMutationScoreIndicator(),
            ],
            'escaped' => $this->getResultsLine($this->resultsCollector->getEscapedExecutionResults()),
            'timeouted' => $this->getResultsLine($this->resultsCollector->getTimedOutExecutionResults()),
            'killed' => $this->getResultsLine($this->resultsCollector->getKilledExecutionResults()),
            'errored' => $this->getResultsLine($this->resultsCollector->getErrorExecutionResults()),
            'syntaxErrors' => $this->getResultsLine($this->resultsCollector->getSyntaxErrorExecutionResults()),
            'uncovered' => $this->onlyCoveredMode ? [] : $this->getResultsLine($this->resultsCollector->getNotCoveredExecutionResults()),
        ];

        return [json_encode($data, JSON_THROW_ON_ERROR)];
    }

    /**
     * @param MutantExecutionResult[] $executionResults
     *
     * @return array<int, array{mutator: array, diff: string, processOutput: string}>
     */
    private function getResultsLine(array $executionResults): array
    {
        $mutatorRows = [];

        foreach ($executionResults as $index => $mutantProcess) {
            $mutatorRows[] = [
                'mutator' => [
                    'mutatorName' => $mutantProcess->getMutatorName(),
                    'originalSourceCode' => $mutantProcess->getOriginalCode(),
                    'mutatedSourceCode' => $mutantProcess->getMutatedCode(),
                    'originalFilePath' => $mutantProcess->getOriginalFilePath(),
                    'originalStartLine' => $mutantProcess->getOriginalStartingLine(),
                ],
                'diff' => $this->convertToUtf8(Str::trimLineReturns($mutantProcess->getMutantDiff())),
                'processOutput' => $this->convertToUtf8(Str::trimLineReturns($mutantProcess->getProcessOutput())),
            ];
        }

        return $mutatorRows;
    }

    private function convertToUtf8(string $string): string
    {
        return mb_convert_encoding($string, 'UTF-8', 'UTF-8');
    }
}
