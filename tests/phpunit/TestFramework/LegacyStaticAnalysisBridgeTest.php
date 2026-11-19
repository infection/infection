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
use Infection\StaticAnalysis\StaticAnalysisToolAdapter;
use Infection\TestFramework\Common\InitialRunProcessFactory;
use Infection\TestFramework\Common\Throwable\InitialTestsFailed;
use Infection\TestFramework\Contracts\InitialRunResults;
use Infection\TestFramework\LegacyStaticAnalysisBridge;
use Infection\Tests\Mutant\MutantBuilder;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(LegacyStaticAnalysisBridge::class)]
final class LegacyStaticAnalysisBridgeTest extends TestCase
{
    private InitialRunProcessFactory&MockObject $processFactory;

    private StaticAnalysisToolAdapter&MockObject $staticAnalysisToolAdapter;

    private LegacyStaticAnalysisBridge $bridge;

    protected function setUp(): void
    {
        $this->processFactory = $this->createMock(InitialRunProcessFactory::class);
        $this->staticAnalysisToolAdapter = $this->createMock(StaticAnalysisToolAdapter::class);
        $this->bridge = new LegacyStaticAnalysisBridge($this->processFactory, $this->staticAnalysisToolAdapter);
    }

    public function test_it_executes_the_initial_run_and_returns_the_output(): void
    {
        $onProgress = static function (): void {};
        $command = ['phpstan', 'analyse', '--configuration=some config.neon'];
        $commandLine = "'phpstan' 'analyse' '--configuration=some config.neon'";
        $startedCommands = [];
        $onStart = static function (string $commandLine) use (&$startedCommands): void {
            $startedCommands[] = $commandLine;
        };
        $process = $this->createMock(Process::class);
        $process
            ->expects($this->once())
            ->method('run')
            ->with($onProgress)
            ->willReturnCallback(function () use (&$startedCommands, $commandLine): int {
                $this->assertSame([$commandLine], $startedCommands);

                return 0;
            })
        ;
        $process->method('getCommandLine')->willReturn($commandLine);
        $process->method('isSuccessful')->willReturn(true);
        $process->method('getOutput')->willReturn('static-analysis output');
        $this->staticAnalysisToolAdapter->method('getInitialRunCommandLine')->willReturn($command);
        $this->processFactory
            ->expects($this->once())
            ->method('create')
            ->with($command, false)
            ->willReturn($process)
        ;

        $actual = $this->bridge->executeInitialRun($onProgress, $onStart);

        $this->assertEquals(new InitialRunResults('static-analysis output', null), $actual);
    }

    public function test_it_reports_initial_run_failures(): void
    {
        $process = $this->createMock(Process::class);
        $process->expects($this->once())->method('run')->with(null);
        $process->method('isSuccessful')->willReturn(false);
        $process->method('getExitCode')->willReturn(3);
        $process->method('getErrorOutput')->willReturn('Invalid configuration');
        $this->staticAnalysisToolAdapter->method('getName')->willReturn('PHPStan');
        $this->staticAnalysisToolAdapter->method('getInitialRunCommandLine')->willReturn(['phpstan', 'analyse']);
        $this->processFactory->method('create')->willReturn($process);

        $this->expectException(InitialTestsFailed::class);
        $this->expectExceptionMessage('Project static analysis must be in a passing state before running Infection.');
        $this->expectExceptionMessage('PHPStan reported an exit code of 3.');
        $this->expectExceptionMessage('Invalid configuration');

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
