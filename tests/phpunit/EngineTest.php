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

namespace Infection\Tests;

use Infection\Configuration\Configuration;
use Infection\Console\ConsoleOutput;
use Infection\Engine;
use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\Events\Application\ApplicationExecutionWasFinished;
use Infection\Metrics\MaxTimeoutCountReached;
use Infection\Metrics\MaxTimeoutsChecker;
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\MinMsiChecker;
use Infection\Metrics\MinMsiCheckFailed;
use Infection\Mutation\MutationGenerator;
use Infection\Process\Runner\InitialStaticAnalysisRunner;
use Infection\Process\Runner\MutationTestingRunner;
use Infection\Resource\Memory\MemoryLimiter;
use Infection\StaticAnalysis\StaticAnalysisToolAdapter;
use Infection\StaticAnalysis\StaticAnalysisToolTypes;
use Infection\TestFramework\Contracts\InitialRunResults;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\Tests\Configuration\ConfigurationBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Process\Process;

#[CoversClass(Engine::class)]
final class EngineTest extends TestCase
{
    private MockObject&TestFramework $testFramework;

    private MockObject&EventDispatcher $eventDispatcher;

    private MockObject&MemoryLimiter $memoryLimiter;

    private MockObject&MutationGenerator $mutationGenerator;

    private MockObject&MutationTestingRunner $mutationTestingRunner;

    private MockObject&MinMsiChecker $minMsiChecker;

    private MockObject&MaxTimeoutsChecker $maxTimeoutsChecker;

    private \PHPUnit\Framework\MockObject\Stub&ConsoleOutput $consoleOutput;

    private MockObject&MetricsCalculator $metricsCalculator;

    protected function setUp(): void
    {
        $this->testFramework = $this->createMock(TestFramework::class);
        $this->eventDispatcher = $this->createMock(EventDispatcher::class);
        $this->memoryLimiter = $this->createMock(MemoryLimiter::class);
        $this->mutationGenerator = $this->createMock(MutationGenerator::class);
        $this->mutationTestingRunner = $this->createMock(MutationTestingRunner::class);
        $this->minMsiChecker = $this->createMock(MinMsiChecker::class);
        $this->maxTimeoutsChecker = $this->createMock(MaxTimeoutsChecker::class);
        $this->consoleOutput = $this->createStub(ConsoleOutput::class);
        $this->metricsCalculator = $this->createMock(MetricsCalculator::class);
    }

    public function test_initial_test_run_fails(): void
    {
        $exception = new RuntimeException('Initial run failed');

        $this->testFramework
            ->expects($this->once())
            ->method('checkRequirements')
        ;
        $this->testFramework
            ->expects($this->once())
            ->method('executeInitialRun')
            ->willThrowException($exception)
        ;

        $this->memoryLimiter->expects($this->never())->method($this->anything());
        $this->mutationGenerator->expects($this->never())->method($this->anything());
        $this->mutationTestingRunner->expects($this->never())->method($this->anything());
        $this->eventDispatcher->expects($this->never())->method($this->anything());

        $this->expectExceptionObject($exception);

        $this->createEngine()->execute();
    }

    public function test_initial_test_run_succeeds(): void
    {
        $initialRunResults = new InitialRunResults(10.0);

        $this->testFramework
            ->expects($this->once())
            ->method('checkRequirements')
        ;
        $this->testFramework
            ->expects($this->once())
            ->method('executeInitialRun')
            ->willReturn($initialRunResults)
        ;
        $this->memoryLimiter
            ->expects($this->once())
            ->method('limitMemory')
            ->with($initialRunResults)
        ;
        $this->mutationGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(false)
            ->willReturn([])
        ;
        $this->mutationTestingRunner
            ->expects($this->once())
            ->method('run')
            ->with([])
        ;
        $this->minMsiChecker
            ->expects($this->once())
            ->method('checkMetrics')
            ->with(1000, 2.0, 3.0, $this->consoleOutput)
        ;
        $this->metricsCalculator
            ->expects($this->once())
            ->method('getTestedMutantsCount')
            ->willReturn(1000)
        ;
        $this->metricsCalculator
            ->expects($this->once())
            ->method('getMutationScoreIndicator')
            ->willReturn(2.0)
        ;
        $this->metricsCalculator
            ->expects($this->once())
            ->method('getCoveredCodeMutationScoreIndicator')
            ->willReturn(3.0)
        ;
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ApplicationExecutionWasFinished::class))
        ;

        $this->createEngine()->execute();
    }

    public function test_memory_limiter_is_applied_after_static_analysis_when_enabled(): void
    {
        $config = ConfigurationBuilder::withMinimalTestData()
            ->withSkipInitialTests(false)
            ->withStaticAnalysisTool(StaticAnalysisToolTypes::PHPSTAN)
            ->withUncovered(true)
            ->build();

        $initialRunResults = new InitialRunResults(20.0);
        $callOrder = [];

        $this->testFramework
            ->method('executeInitialRun')
            ->willReturn($initialRunResults)
        ;

        $staticAnalysisProcess = $this->createMock(Process::class);
        $staticAnalysisProcess
            ->expects($this->once())
            ->method('isSuccessful')
            ->willReturn(true)
        ;

        $initialStaticAnalysisRunner = $this->createMock(InitialStaticAnalysisRunner::class);
        $initialStaticAnalysisRunner
            ->expects($this->once())
            ->method('run')
            ->willReturn($staticAnalysisProcess)
        ;

        $staticAnalysisToolAdapter = $this->createStub(StaticAnalysisToolAdapter::class);

        $this->memoryLimiter
            ->expects($this->once())
            ->method('limitMemory')
            ->with($initialRunResults)
            ->willReturnCallback(static function () use (&$callOrder): void {
                $callOrder[] = 'limitMemory';
            })
        ;
        $this->mutationGenerator
            ->expects($this->once())
            ->method('generate')
            ->willReturnCallback(static function () use (&$callOrder): array {
                $callOrder[] = 'generate';

                return [];
            })
        ;
        $this->mutationTestingRunner
            ->expects($this->once())
            ->method('run')
            ->with([])
        ;
        $this->metricsCalculator
            ->method('getTestedMutantsCount')
            ->willReturn(100)
        ;
        $this->metricsCalculator
            ->method('getMutationScoreIndicator')
            ->willReturn(80.0)
        ;
        $this->metricsCalculator
            ->method('getCoveredCodeMutationScoreIndicator')
            ->willReturn(85.0)
        ;

        $this->createEngine($config, $initialStaticAnalysisRunner, $staticAnalysisToolAdapter)->execute();

        $this->assertSame(['limitMemory', 'generate'], $callOrder);
    }

    public function test_memory_limiter_receives_null_when_initial_tests_are_skipped(): void
    {
        $config = ConfigurationBuilder::withMinimalTestData()
            ->withSkipInitialTests(true)
            ->withUncovered(true)
            ->build();

        $this->testFramework
            ->expects($this->once())
            ->method('checkRequirements')
        ;
        $this->testFramework
            ->expects($this->never())
            ->method('executeInitialRun')
        ;
        $this->memoryLimiter
            ->expects($this->once())
            ->method('limitMemory')
            ->with(null)
        ;
        $this->mutationGenerator
            ->method('generate')
            ->willReturn([])
        ;
        $this->metricsCalculator
            ->method('getTestedMutantsCount')
            ->willReturn(0)
        ;
        $this->metricsCalculator
            ->method('getMutationScoreIndicator')
            ->willReturn(0.0)
        ;
        $this->metricsCalculator
            ->method('getCoveredCodeMutationScoreIndicator')
            ->willReturn(0.0)
        ;

        $this->createEngine($config)->execute();
    }

    public function test_max_timeouts_checker_receives_correct_timed_out_count(): void
    {
        $config = ConfigurationBuilder::withMinimalTestData()
            ->withSkipInitialTests(true)
            ->withUncovered(true)
            ->build();

        $this->mutationGenerator
            ->method('generate')
            ->willReturn([])
        ;
        $this->metricsCalculator
            ->expects($this->once())
            ->method('getTimedOutCount')
            ->willReturn(42)
        ;
        $this->metricsCalculator
            ->method('getTestedMutantsCount')
            ->willReturn(0)
        ;
        $this->metricsCalculator
            ->method('getMutationScoreIndicator')
            ->willReturn(0.0)
        ;
        $this->metricsCalculator
            ->method('getCoveredCodeMutationScoreIndicator')
            ->willReturn(0.0)
        ;
        $this->maxTimeoutsChecker
            ->expects($this->once())
            ->method('checkTimeouts')
            ->with(42)
        ;

        $this->createEngine($config)->execute();
    }

    public function test_application_execution_was_finished_is_dispatched_when_max_timeouts_checker_throws(): void
    {
        $config = ConfigurationBuilder::withMinimalTestData()
            ->withSkipInitialTests(true)
            ->withUncovered(true)
            ->build();

        $this->mutationGenerator
            ->method('generate')
            ->willReturn([])
        ;
        $this->metricsCalculator
            ->method('getTimedOutCount')
            ->willReturn(100)
        ;
        $this->maxTimeoutsChecker
            ->method('checkTimeouts')
            ->willThrowException(MaxTimeoutCountReached::create(10, 100))
        ;
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ApplicationExecutionWasFinished::class))
        ;

        $this->expectException(MaxTimeoutCountReached::class);

        $this->createEngine($config)->execute();
    }

    public function test_application_execution_was_finished_is_dispatched_when_min_msi_checker_throws(): void
    {
        $config = ConfigurationBuilder::withMinimalTestData()
            ->withSkipInitialTests(true)
            ->withUncovered(true)
            ->build();

        $this->mutationGenerator
            ->method('generate')
            ->willReturn([])
        ;
        $this->metricsCalculator
            ->method('getTimedOutCount')
            ->willReturn(0)
        ;
        $this->metricsCalculator
            ->method('getTestedMutantsCount')
            ->willReturn(100)
        ;
        $this->metricsCalculator
            ->method('getMutationScoreIndicator')
            ->willReturn(50.0)
        ;
        $this->metricsCalculator
            ->method('getCoveredCodeMutationScoreIndicator')
            ->willReturn(55.0)
        ;
        $this->minMsiChecker
            ->method('checkMetrics')
            ->willThrowException(MinMsiCheckFailed::createForMsi(80.0, 50.0))
        ;
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ApplicationExecutionWasFinished::class))
        ;

        $this->expectException(MinMsiCheckFailed::class);

        $this->createEngine($config)->execute();
    }

    private function createEngine(
        ?Configuration $config = null,
        ?InitialStaticAnalysisRunner $initialStaticAnalysisRunner = null,
        ?StaticAnalysisToolAdapter $staticAnalysisToolAdapter = null,
    ): Engine {
        return new Engine(
            $config ?? ConfigurationBuilder::withMinimalTestData()
                ->withSkipInitialTests(false)
                ->withUncovered(true)
                ->build(),
            $this->testFramework,
            $this->eventDispatcher,
            $this->memoryLimiter,
            $this->mutationGenerator,
            $this->mutationTestingRunner,
            $this->minMsiChecker,
            $this->maxTimeoutsChecker,
            $this->consoleOutput,
            $this->metricsCalculator,
            $initialStaticAnalysisRunner,
            $staticAnalysisToolAdapter,
        );
    }
}
