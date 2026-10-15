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

namespace Infection\Tests\TestFramework;

use Infection\Process\Factory\LazyMutantProcessFactory;
use Infection\Process\MutantProcess;
use Infection\Process\Runner\InitialStaticAnalysisRunFailed;
use Infection\Process\Runner\InitialStaticAnalysisRunner;
use Infection\StaticAnalysis\StaticAnalysisToolAdapter;
use Infection\TestFramework\Contracts\InitialRunResults;
use Infection\TestFramework\LegacyStaticAnalysisBridge;
use Infection\Tests\Mutant\MutantBuilder;
use Infection\Tests\TestFramework\Contracts\CompletedProcessBuilder;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(LegacyStaticAnalysisBridge::class)]
final class LegacyStaticAnalysisBridgeTest extends TestCase
{
    private InitialStaticAnalysisRunner&MockObject $runner;

    private StaticAnalysisToolAdapter&MockObject $staticAnalysisToolAdapter;

    private LegacyStaticAnalysisBridge $bridge;

    protected function setUp(): void
    {
        $this->runner = $this->createMock(InitialStaticAnalysisRunner::class);
        $this->staticAnalysisToolAdapter = $this->createMock(StaticAnalysisToolAdapter::class);
        $this->bridge = new LegacyStaticAnalysisBridge($this->runner, $this->staticAnalysisToolAdapter);
    }

    public function test_it_executes_the_initial_run_and_returns_empty_results(): void
    {
        $this->runner
            ->expects($this->once())
            ->method('run')
        ;

        $this->assertEquals(new InitialRunResults('', null), $this->bridge->executeInitialRun());
    }

    public function test_it_preserves_initial_run_failures(): void
    {
        $process = CompletedProcessBuilder::withMinimalTestData()
            ->withExitCode(3)
            ->build()
        ;
        $failure = InitialStaticAnalysisRunFailed::fromCompletedProcessAndAdapter($process, 'PHPStan');
        $this->runner
            ->expects($this->once())
            ->method('run')
            ->willThrowException($failure)
        ;

        $this->expectExceptionObject($failure);

        $this->bridge->executeInitialRun();
    }

    public function test_it_exposes_the_adapter_identity_and_requirements(): void
    {
        $this->staticAnalysisToolAdapter
            ->expects($this->once())
            ->method('getName')
            ->willReturn('PHPStan')
        ;
        $this->staticAnalysisToolAdapter
            ->expects($this->once())
            ->method('getVersion')
            ->willReturn('2.1.17')
        ;
        $this->staticAnalysisToolAdapter
            ->expects($this->once())
            ->method('assertMinimumVersionSatisfied')
        ;

        $this->assertSame('PHPStan', $this->bridge->getName());
        $this->assertSame('2.1.17', $this->bridge->getVersion());
        $this->bridge->checkRequirements();
    }

    public function test_it_creates_a_mutant_process_without_follow_up_processes(): void
    {
        $mutant = MutantBuilder::withMinimalTestData()
            ->build()
        ;
        $process = $this->createStub(MutantProcess::class);
        $factory = $this->createMock(LazyMutantProcessFactory::class);
        $factory
            ->expects($this->once())
            ->method('create')
            ->with($mutant)
            ->willReturn($process)
        ;
        $this->staticAnalysisToolAdapter
            ->expects($this->once())
            ->method('createMutantProcessFactory')
            ->willReturn($factory)
        ;

        $container = $this->bridge->test($mutant);

        $this->assertSame($process, $container->getCurrent());
        $this->assertFalse($container->hasNext());
    }
}
