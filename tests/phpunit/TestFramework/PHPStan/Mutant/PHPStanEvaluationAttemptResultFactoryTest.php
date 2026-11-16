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

namespace Infection\Tests\TestFramework\PHPStan\Mutant;

use Infection\Mutant\DetectionStatus;
use Infection\Mutant\EvaluationAttemptResult;
use Infection\Mutant\EvaluationProcessDetails;
use Infection\Process\MutantProcess;
use Infection\TestFramework\Common\MutantProcessDetectionStatusResolver;
use Infection\TestFramework\PHPStan\Mutant\PHPStanEvaluationAttemptResultFactory;
use Infection\TestFramework\PHPStan\Mutant\PHPStanMutantDetectionStatusResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[CoversClass(PHPStanEvaluationAttemptResultFactory::class)]
final class PHPStanEvaluationAttemptResultFactoryTest extends TestCase
{
    #[DataProvider('detectionStatusProvider')]
    public function test_it_classifies_the_attempt_and_retains_its_evidence(?int $exitCode, bool $timedOut, DetectionStatus $status): void
    {
        $factory = new PHPStanEvaluationAttemptResultFactory(new PHPStanMutantDetectionStatusResolver());
        $process = $this->createStub(Process::class);
        $process->method('isTerminated')->willReturn(true);
        $process->method('getCommandLine')->willReturn('evaluate mutant');
        $process->method('getOutput')->willReturn("  analysis output\n");
        $process->method('getErrorOutput')->willReturn('diagnostics  ');
        $process->method('getStartTime')->willReturn(10.0);
        $process->method('getExitCode')->willReturn($exitCode);
        $mutantProcess = $this->createStub(MutantProcess::class);
        $mutantProcess->method('getProcess')->willReturn($process);
        $mutantProcess->method('isTimedOut')->willReturn($timedOut);
        $mutantProcess->method('getRuntime')->willReturn(1.5);
        $expected = new EvaluationAttemptResult(
            'PHPStan',
            $status,
            "analysis output\n\n\ndiagnostics",
            new EvaluationProcessDetails('evaluate mutant', 1.5),
        );

        $actual = $factory->createFromProcess($mutantProcess);

        $this->assertEquals($expected, $actual);
    }

    public static function detectionStatusProvider(): iterable
    {
        yield 'successful analysis escapes' => [0, false, DetectionStatus::ESCAPED];

        yield 'analysis detects a problem' => [1, false, DetectionStatus::KILLED_BY_STATIC_ANALYSIS];

        yield 'maximum detection exit code' => [100, false, DetectionStatus::KILLED_BY_STATIC_ANALYSIS];

        yield 'first process error exit code' => [101, false, DetectionStatus::ERROR];

        yield 'process failed' => [152, false, DetectionStatus::ERROR];

        yield 'terminated process without an exit code' => [null, false, DetectionStatus::KILLED_BY_STATIC_ANALYSIS];

        yield 'timeout takes precedence over the exit code' => [0, true, DetectionStatus::TIMED_OUT];
    }

    public function test_it_uses_the_injected_framework_resolvers_status(): void
    {
        $resolver = $this->createMock(MutantProcessDetectionStatusResolver::class);
        $resolver->expects($this->once())->method('resolve')->with('stdout', 'stderr', null, true)
            ->willReturn(DetectionStatus::SKIPPED)
        ;
        $process = $this->createStub(Process::class);
        $process->method('isTerminated')->willReturn(true);
        $process->method('getOutput')->willReturn('stdout');
        $process->method('getErrorOutput')->willReturn('stderr');
        $process->method('getExitCode')->willReturn(null);
        $attempt = $this->createStub(MutantProcess::class);
        $attempt->method('getProcess')->willReturn($process);
        $attempt->method('isTimedOut')->willReturn(true);
        $factory = new PHPStanEvaluationAttemptResultFactory($resolver);

        $result = $factory->createFromProcess($attempt);

        $this->assertSame(DetectionStatus::SKIPPED, $result->detectionStatus);
    }
}
