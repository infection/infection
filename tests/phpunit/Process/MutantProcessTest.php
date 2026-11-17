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

namespace Infection\Tests\Process;

use DuoClock\TimeSpy;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\EvaluationAttemptResult;
use Infection\Mutant\EvaluationAttemptResultFactory;
use Infection\Mutant\EvaluationProcessDetails;
use Infection\Mutant\Mutant;
use Infection\Process\DryRunProcess;
use Infection\Process\MutantProcess;
use Infection\Tests\Mutant\MutantBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(MutantProcess::class)]
final class MutantProcessTest extends TestCase
{
    private Stub&Process $processStub;

    private Mutant $mutant;

    private MutantProcess $mutantProcess;

    private TimeSpy $clock;

    protected function setUp(): void
    {
        $this->processStub = $this->createStub(Process::class);
        $this->mutant = MutantBuilder::withMinimalTestData()->build();
        $evaluationAttemptResultFactory = $this->createStub(EvaluationAttemptResultFactory::class);
        $this->clock = new TimeSpy(100.0);

        $this->mutantProcess = new MutantProcess(
            $this->processStub,
            $this->mutant,
            $evaluationAttemptResultFactory,
            $this->clock,
        );
    }

    public function test_it_exposes_its_state(): void
    {
        $this->assertMutantProcessStateIs(
            $this->mutantProcess,
            $this->processStub,
            $this->mutant,
            false,
        );
    }

    public function test_it_can_be_marked_as_timed_out(): void
    {
        $this->mutantProcess->markAsTimedOut();

        $this->assertMutantProcessStateIs(
            $this->mutantProcess,
            $this->processStub,
            $this->mutant,
            true,
        );
    }

    public function test_it_classifies_a_completed_attempt_once_and_retains_the_same_result(): void
    {
        $expected = new EvaluationAttemptResult(
            'PHPUnit',
            DetectionStatus::ESCAPED,
            'output',
            new EvaluationProcessDetails('phpunit', 1.25),
        );
        $factory = $this->createMock(EvaluationAttemptResultFactory::class);
        $process = new MutantProcess($this->processStub, $this->mutant, $factory);
        $factory->expects($this->once())->method('createFromProcess')->with($this->identicalTo($process))->willReturn($expected);
        $process->markAsFinished();

        $this->assertSame($expected, $process->getEvaluationAttemptResult());
        $this->assertSame($expected, $process->getEvaluationAttemptResult());
    }

    public function test_it_cannot_classify_an_unfinished_attempt(): void
    {
        $factory = $this->createMock(EvaluationAttemptResultFactory::class);
        $factory->expects($this->never())->method('createFromProcess');
        $process = new MutantProcess($this->processStub, $this->mutant, $factory);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot classify an evaluation before its process has finished.');

        $process->getEvaluationAttemptResult();
    }

    public function test_it_simulates_a_completed_process_for_a_dry_run(): void
    {
        $underlying = new Process(['must-not-run']);
        $attempt = new MutantProcess($underlying, $this->mutant, $this->createStub(EvaluationAttemptResultFactory::class));

        $attempt->simulate();

        $this->assertFalse($underlying->isStarted());
        $this->assertInstanceOf(DryRunProcess::class, $attempt->getProcess());
        $this->assertGreaterThan(0.0, $attempt->getFinishedAt());
        $this->assertSame(0.0, $attempt->getRuntime());
    }

    public function test_it_cannot_simulate_an_already_completed_process(): void
    {
        $this->mutantProcess->markAsFinished();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot simulate an already completed evaluation.');

        $this->mutantProcess->simulate();
    }

    public function test_it_exposes_the_completed_process_runtime(): void
    {
        $this->processStub->method('getStartTime')->willReturn(10.0);
        $this->mutantProcess->markAsFinished();

        $this->assertSame($this->mutantProcess->getFinishedAt() - 10.0, $this->mutantProcess->getRuntime());
    }

    public function test_it_records_when_completion_is_observed(): void
    {
        $this->assertSame(0.0, $this->mutantProcess->getFinishedAt());

        $this->clock->usleep(250_000);
        $this->mutantProcess->markAsFinished();
        $this->clock->sleep(2);

        $this->assertSame(100.25, $this->mutantProcess->getFinishedAt());
    }

    private function assertMutantProcessStateIs(
        MutantProcess $mutantProcess,
        Process $expectedProcess,
        Mutant $expectedMutant,
        bool $expectedTimedOut,
    ): void {
        $this->assertSame($expectedProcess, $mutantProcess->getProcess());
        $this->assertSame($expectedMutant, $mutantProcess->getMutant());
        $this->assertSame($expectedTimedOut, $mutantProcess->isTimedOut());
    }
}
