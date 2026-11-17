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

namespace Infection\Tests\TestFramework\Common;

use Infection\Mutant\DetectionStatus;
use Infection\Process\MutantProcess;
use Infection\Process\MutantProcessContainer;
use Infection\TestFramework\Common\CombinedMutantEvaluationPipe;
use Infection\Tests\Mutant\MutantBuilder;
use Infection\Tests\Mutant\MutantExecutionResultBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(CombinedMutantEvaluationPipe::class)]
final class CombinedMutantEvaluationPipeTest extends TestCase
{
    public function test_it_preserves_every_process_in_nested_framework_evaluations(): void
    {
        $mutant = MutantBuilder::withMinimalTestData()->build();
        $first = $this->process(DetectionStatus::ESCAPED);
        $second = $this->process(DetectionStatus::ESCAPED);
        $third = $this->process(DetectionStatus::ESCAPED);
        $fourth = $this->process(DetectionStatus::KILLED_BY_STATIC_ANALYSIS);
        $fifth = $this->process(DetectionStatus::ESCAPED);
        $firstPipe = MutantProcessContainer::from($mutant, static fn (): MutantProcess => $first)
            ->append(static fn (): MutantProcess => $second)
        ;
        $secondPipe = MutantProcessContainer::from($mutant, static fn (): MutantProcess => $third)
            ->append(static fn (): MutantProcess => $fourth)
        ;
        $pipe = $firstPipe->merge($secondPipe)->merge(
            MutantProcessContainer::from($mutant, static fn (): MutantProcess => $fifth),
        );

        $this->assertSame($mutant, $pipe->getMutant());
        $this->assertTrue($pipe->isCold());
        $this->assertTrue($firstPipe->isCold());
        $this->assertTrue($secondPipe->isCold());
        $this->assertSame($first, $pipe->getCurrent());
        $this->assertFalse($pipe->isCold());
        $this->assertTrue($pipe->hasNext());
        $this->assertSame($second, $pipe->createNext());
        $this->assertTrue($pipe->hasNext());
        $this->assertSame($third, $pipe->createNext());
        $this->assertTrue($pipe->hasNext());
        $this->assertSame($fourth, $pipe->createNext());
        $this->assertFalse($pipe->hasNext());
        $this->assertSame($fourth, $pipe->getCurrent());
    }

    #[DataProvider('completedStatusProvider')]
    public function test_it_only_advances_to_another_pipe_after_escape(DetectionStatus $status, bool $hasNext): void
    {
        $mutant = MutantBuilder::withMinimalTestData()->build();
        $first = $this->process($status);
        $pipe = CombinedMutantEvaluationPipe::from(
            MutantProcessContainer::from($mutant, static fn (): MutantProcess => $first),
            MutantProcessContainer::from($mutant, function (): MutantProcess {
                $this->fail('Checking continuation must not create the next process.');
            }),
        );

        $this->assertSame($hasNext, $pipe->hasNext());
    }

    #[DataProvider('startedPipeProvider')]
    public function test_it_rejects_merging_either_started_pipe(bool $startFirst, bool $mergeCombined): void
    {
        $mutant = MutantBuilder::withMinimalTestData()->build();
        $process = $this->process(DetectionStatus::ESCAPED);
        $first = MutantProcessContainer::from($mutant, static fn (): MutantProcess => $process);
        $second = MutantProcessContainer::from($mutant, static fn (): MutantProcess => $process);
        $third = MutantProcessContainer::from($mutant, static fn (): MutantProcess => $process);
        $left = $mergeCombined ? $first->merge($second) : $first;
        $right = $mergeCombined ? $third : $second;
        ($startFirst ? $left : $right)->getCurrent();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot merge a mutant evaluation pipe after evaluation has started.');

        $left->merge($right);
    }

    public static function completedStatusProvider(): iterable
    {
        yield 'escaped' => [DetectionStatus::ESCAPED, true];

        yield 'killed by tests' => [DetectionStatus::KILLED_BY_TESTS, false];

        yield 'killed by analysis' => [DetectionStatus::KILLED_BY_STATIC_ANALYSIS, false];

        yield 'timeout' => [DetectionStatus::TIMED_OUT, false];

        yield 'error' => [DetectionStatus::ERROR, false];

        yield 'syntax error' => [DetectionStatus::SYNTAX_ERROR, false];
    }

    public static function startedPipeProvider(): iterable
    {
        yield 'first single pipe started' => [true, false];

        yield 'second single pipe started' => [false, false];

        yield 'combined pipe started' => [true, true];

        yield 'pipe appended to combined started' => [false, true];
    }

    private function process(DetectionStatus $status): MutantProcess
    {
        $process = $this->createStub(MutantProcess::class);
        $result = MutantExecutionResultBuilder::withMinimalTestData()->withDetectionStatus($status)->build();
        $process->method('getMutantExecutionResult')->willReturn($result);

        return $process;
    }
}
