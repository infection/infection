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

use DuoClock\TimeSpy;
use Infection\TestFramework\Common\MutantProcess;
use Infection\TestFramework\Common\MutantProcessDetectionStatusResolver;
use Infection\TestFramework\Contracts\DetectionStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[CoversClass(MutantProcess::class)]
final class MutantProcessTest extends TestCase
{
    public function test_it_exposes_the_finished_process_result(): void
    {
        $process = $this->createStub(Process::class);
        $process->method('getCommandLine')->willReturn('mago analyze');
        $process->method('getOutput')->willReturn('stdout');
        $process->method('getErrorOutput')->willReturn('stderr');
        $process->method('getExitCode')->willReturn(1);
        $process->method('getStartTime')->willReturn(10.);

        $resolver = $this->createMock(MutantProcessDetectionStatusResolver::class);
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with('stdout', 'stderr', 1, true)
            ->willReturn(DetectionStatus::KILLED_BY_STATIC_ANALYSIS)
        ;

        $clock = $this->createStub(TimeSpy::class);
        $clock->method('microtime')->willReturn(12.);

        $mutantProcess = new MutantProcess($process, $resolver, $clock);
        $mutantProcess->markAsTimedOut();
        $mutantProcess->markAsFinished();

        $result = $mutantProcess->getResult();

        $this->assertSame('mago analyze', $result->commandLine);
        $this->assertSame('stdout', $result->stdout);
        $this->assertSame('stderr', $result->stderr);
        $this->assertSame(10., $result->startedAt);
        $this->assertSame(12., $result->finishedAt);
        $this->assertSame(DetectionStatus::KILLED_BY_STATIC_ANALYSIS, $result->detectionStatus);
    }
}
