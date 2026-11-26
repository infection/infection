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

namespace Infection\Tests\Mutant;

use Closure;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\DirectEvaluationResult;
use Infection\Mutant\Mutant;
use Infection\Mutant\MutantExecutionResult;
use Infection\Process\MutantProcess;
use Later\Interfaces\Deferred;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[CoversClass(MutantExecutionResult::class)]
final class MutantExecutionResultTest extends TestCase
{
    public function test_it_retains_attempt_evidence_without_forcing_deferred_values(): void
    {
        $diff = $this->createMock(Deferred::class);
        $diff->expects($this->never())->method('get');
        $code = $this->createMock(Deferred::class);
        $code->expects($this->never())->method('get');
        $source = MutantBuilder::withCompleteTestData()->build();
        $mutant = $this->createStub(Mutant::class);
        $mutant->method('getMutation')->willReturn($source->getMutation());
        $mutant->method('getDiff')->willReturn($diff);
        $mutant->method('getMutatedCode')->willReturn($code);
        $mutant->method('getPrettyPrintedOriginalCode')->willReturn('original source');
        $mutant->method('getTests')->willReturn($source->getTests());
        $process = $this->createStub(Process::class);
        $process->method('getCommandLine')->willReturn('phpstan');
        $analysis = $this->createStub(MutantProcess::class);
        $analysis->method('getProcess')->willReturn($process);
        $analysis->method('getResultOutput')->willReturn('analysis failed');
        $analysis->method('getRuntime')->willReturn(0.5);
        $history = [
            new DirectEvaluationResult('In-memory evaluator', DetectionStatus::ESCAPED, 'test output'),
            $analysis,
        ];

        $result = MutantExecutionResult::createFromEvaluation($mutant, DetectionStatus::ERROR, $history);

        $this->assertSame(DetectionStatus::ERROR, $result->getDetectionStatus());
        $this->assertSame($history, $result->getEvaluationHistory());
        $this->assertSame('phpstan', $result->getProcessCommandLine());
        $this->assertSame('analysis failed', $result->getProcessOutput());
        $this->assertSame(0.5, $result->getProcessRuntime());
        $this->assertSame('original source', $result->getOriginalCode());
        $this->assertSame($source->getTests(), $result->getTests());
        $this->assertSame($source->getMutation()->getHash(), $result->getMutantHash());
        $this->assertSame($source->getMutation()->getMutatorClass(), $result->getMutatorClass());
        $this->assertSame($source->getMutation()->getMutatorName(), $result->getMutatorName());
        $this->assertSame($source->getMutation()->getOriginalFilePath(), $result->getOriginalFilePath());
        $this->assertSame($source->getMutation()->getOriginalStartingLine(), $result->getOriginalStartingLine());
        $this->assertSame($source->getMutation()->getOriginalEndingLine(), $result->getOriginalEndingLine());
    }

    /**
     * @param Closure(Mutant): MutantExecutionResult $createResult
     */
    #[DataProvider('processFreeResultProvider')]
    public function test_results_without_a_process_have_no_evaluation_history(Closure $createResult, DetectionStatus $status): void
    {
        $result = $createResult(MutantBuilder::withMinimalTestData()->build());

        $this->assertSame([], $result->getEvaluationHistory());
        $this->assertSame($status, $result->getDetectionStatus());
        $this->assertSame('', $result->getProcessCommandLine());
        $this->assertSame('', $result->getProcessOutput());
        $this->assertSame(0.0, $result->getProcessRuntime());
    }

    public static function processFreeResultProvider(): iterable
    {
        yield 'uncovered' => [MutantExecutionResult::createFromNonCoveredMutant(...), DetectionStatus::NOT_COVERED];

        yield 'skipped' => [MutantExecutionResult::createFromTimeSkippedMutant(...), DetectionStatus::SKIPPED];

        yield 'ignored' => [MutantExecutionResult::createFromIgnoredMutant(...), DetectionStatus::IGNORED];
    }
}
