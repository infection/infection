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
use Infection\Process\DryRunProcess;
use Infection\Process\MutantProcess;
use Infection\TestFramework\Contracts\Mutant\MutantProcessDetectionStatusResolver;
use Infection\Tests\Mutant\MutantBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
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
            includeErrorOutput: false,
        );
    }

    public function test_it_exposes_its_state(): void
    {
        $this->assertSame('PHPUnit', $this->mutantProcess->getTestFrameworkName());
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

    public function test_it_reports_the_time_between_start_and_completion(): void
    {
        $this->processStub->method('getStartTime')->willReturn(10.0);
        $this->mutantProcess->markAsFinished();
        $expected = $this->mutantProcess->getFinishedAt() - 10.0;

        $this->assertSame($expected, $this->mutantProcess->getRuntime());
    }

    public function test_it_reports_zero_runtime_for_a_completed_dry_run_process(): void
    {
        $process = new DryRunProcess(['phpunit']);
        $resolver = $this->createStub(MutantProcessDetectionStatusResolver::class);
        $mutantProcess = new MutantProcess(
            $process,
            $this->mutant,
            $resolver,
            'PHPUnit',
            includeErrorOutput: false,
        );
        $process->start();
        $mutantProcess->markAsFinished();

        $this->assertSame($process, $mutantProcess->getProcess());
        $this->assertGreaterThan(0.0, $mutantProcess->getFinishedAt());
        $this->assertSame(0.0, $mutantProcess->getRuntime());
    }

    public function test_it_resolves_detection_status_once_after_the_process_finishes(): void
    {
        $this->processStub->method('getOutput')->willReturn('output');
        $this->processStub->method('getExitCode')->willReturn(0);
        $resolver = $this->createMock(MutantProcessDetectionStatusResolver::class);
        $resolver->expects($this->once())->method('resolve')->with('output', 0, true)->willReturn(DetectionStatus::ESCAPED);
        $process = new MutantProcess($this->processStub, $this->mutant, $resolver, 'PHPUnit', includeErrorOutput: false);
        $process->markAsTimedOut();
        $process->markAsFinished();

        $this->assertSame(DetectionStatus::ESCAPED, $process->getResult());
        $this->assertSame(DetectionStatus::ESCAPED, $process->getResult());
    }

    #[DataProvider('resultOutputProvider')]
    public function test_it_reads_the_configured_output_streams(bool $includeErrorOutput, string $expected): void
    {
        $this->processStub->method('getOutput')->willReturn("test output\n");
        $this->processStub->method('getErrorOutput')->willReturn("stderr\n");
        $resolver = $this->createStub(MutantProcessDetectionStatusResolver::class);
        $process = new MutantProcess($this->processStub, $this->mutant, $resolver, 'Evaluator', $includeErrorOutput);
        $process->markAsFinished();

        $this->assertSame($expected, $process->getResultOutput());
    }

    public static function resultOutputProvider(): iterable
    {
        yield 'test framework stdout' => [false, "test output\n"];

        yield 'static analysis stdout and stderr' => [true, "test output\n\n\nstderr"];
    }

    public function test_it_cannot_resolve_detection_status_before_the_process_finishes(): void
    {
        $resolver = $this->createMock(MutantProcessDetectionStatusResolver::class);
        $resolver->expects($this->never())->method('resolve');
        $process = new MutantProcess($this->processStub, $this->mutant, $resolver, 'PHPUnit', includeErrorOutput: false);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot resolve the detection status before the mutant process has finished.');

        $process->getResult();
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
