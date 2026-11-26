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

use Infection\Mutant\DetectionStatus;
use Infection\Mutant\Mutant;
use Infection\Process\MutantProcess;
use Infection\TestFramework\Common\Process\DryRunProcess;
use Infection\TestFramework\Contracts\Mutant\MutantProcessDetectionStatusResolver\MutantProcessDetectionStatusResolver;
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

    protected function setUp(): void
    {
        $this->processStub = $this->createStub(Process::class);
        $this->mutant = MutantBuilder::withMinimalTestData()->build();
        $resolver = $this->createStub(MutantProcessDetectionStatusResolver::class);

        $this->mutantProcess = new MutantProcess(
            $this->processStub,
            $this->mutant,
            $resolver,
            'PHPUnit',
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

    public function test_it_exposes_the_test_framework_name(): void
    {
        $this->assertSame('PHPUnit', $this->mutantProcess->getTestFrameworkName());
    }

    public function test_it_exposes_the_stop_signal_when_the_process_was_stopped(): void
    {
        $this->processStub->method('hasBeenStopped')->willReturn(true);
        $this->processStub->method('getStopSignal')->willReturn(19);

        $this->assertSame(19, $this->mutantProcess->getStopSignal());
    }

    public function test_it_has_no_stop_signal_when_the_process_was_not_stopped(): void
    {
        $this->processStub->method('hasBeenStopped')->willReturn(false);

        $this->assertNull($this->mutantProcess->getStopSignal());
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
        $expected = DetectionStatus::ESCAPED;
        $this->processStub->method('getExitCode')->willReturn(0);
        $this->processStub->method('getOutput')->willReturn('stdout');
        $this->processStub->method('getErrorOutput')->willReturn('stderr');
        $this->processStub->method('hasBeenStopped')->willReturn(true);
        $this->processStub->method('getStopSignal')->willReturn(19);
        $resolver = $this->createMock(MutantProcessDetectionStatusResolver::class);
        $resolver->expects($this->once())->method('tryToResolve')->with('stdout', 'stderr', 0, false, 19)->willReturn($expected);
        $process = new MutantProcess($this->processStub, $this->mutant, $resolver, 'PHPUnit');
        $process->markAsFinished();

        $this->assertSame($expected, $process->getResult());
        $this->assertSame($expected, $process->getResult());
    }

    public function test_it_rejects_a_completed_process_with_no_exit_code(): void
    {
        $this->processStub->method('getExitCode')->willReturn(null);
        $this->mutantProcess->markAsFinished();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A completed process must have an exit code.');

        $this->mutantProcess->getResult();
    }

    public function test_it_reports_stdout_for_test_frameworks(): void
    {
        $this->processStub->method('getOutput')->willReturn(" test output\n");
        $this->processStub->method('getErrorOutput')->willReturn('stderr');

        $this->assertSame(" test output\n", $this->mutantProcess->getResultOutput());
    }

    public function test_it_can_include_stderr_in_the_reported_output(): void
    {
        $this->processStub->method('getOutput')->willReturn(" analysis output\n");
        $this->processStub->method('getErrorOutput')->willReturn('diagnostics  ');
        $process = new MutantProcess(
            $this->processStub,
            $this->mutant,
            $this->createStub(MutantProcessDetectionStatusResolver::class),
            'Mago',
            includeErrorOutput: true,
        );

        $this->assertSame("analysis output\n\n\ndiagnostics", $process->getResultOutput());
    }

    public function test_it_cannot_classify_an_unfinished_attempt(): void
    {
        $factory = $this->createMock(MutantProcessDetectionStatusResolver::class);
        $factory->expects($this->never())->method('tryToResolve');
        $process = new MutantProcess($this->processStub, $this->mutant, $factory, 'PHPUnit');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot classify an evaluation before its process has finished.');

        $process->getResult();
    }

    public function test_it_simulates_a_completed_process_for_a_dry_run(): void
    {
        $underlying = new Process(['must-not-run']);
        $attempt = new MutantProcess(
            $underlying,
            $this->mutant,
            $this->createStub(MutantProcessDetectionStatusResolver::class),
            'PHPUnit',
        );

        $attempt->simulate();

        $this->assertFalse($underlying->isStarted());
        $this->assertInstanceOf(DryRunProcess::class, $attempt->getProcess());
        $this->assertNull($attempt->getStopSignal());
        $this->assertGreaterThan(0.0, $attempt->getFinishedAt());
        $this->assertSame(0.0, $attempt->getRuntime());
    }

    public function test_it_simulates_a_process_prepared_for_dry_run(): void
    {
        $underlying = DryRunProcess::fromProcess(new Process(['must-not-run']));
        $attempt = new MutantProcess(
            $underlying,
            $this->mutant,
            $this->createStub(MutantProcessDetectionStatusResolver::class),
            'PHPUnit',
        );

        $attempt->simulate();

        $this->assertSame($underlying, $attempt->getProcess());
        $this->assertGreaterThan(0.0, $attempt->getFinishedAt());
        $this->assertSame(0.0, $attempt->getRuntime());
    }

    public function test_it_cannot_simulate_a_started_process(): void
    {
        $this->processStub->method('isStarted')->willReturn(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot simulate a process after it has started.');

        $this->mutantProcess->simulate();
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
