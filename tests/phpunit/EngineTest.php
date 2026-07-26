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

use Infection\AbstractTestFramework\TestFrameworkAdapter;
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
use Infection\Process\Runner\InitialTestsFailed;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\Process\Runner\MutationTestingRunner;
use Infection\Resource\Memory\MemoryLimiter;
use Infection\StaticAnalysis\StaticAnalysisToolAdapter;
use Infection\StaticAnalysis\StaticAnalysisToolTypes;
use Infection\TestFramework\Coverage\CoverageChecker;
use Infection\TestFramework\TestFrameworkExtraOptionsFilter;
use Infection\Tests\Configuration\ConfigurationBuilder;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(Engine::class)]
final class EngineTest extends TestCase
{
    private MockObject&TestFrameworkAdapter $adapter;

    private MockObject&CoverageChecker $coverageChecker;

    private MockObject&EventDispatcher $eventDispatcher;

    private MockObject&InitialTestsRunner $initialTestsRunner;

    private MockObject&MemoryLimiter $memoryLimiter;

    private MockObject&MutationGenerator $mutationGenerator;

    private MockObject&MutationTestingRunner $mutationTestingRunner;

    private MockObject&MinMsiChecker $minMsiChecker;

    private MockObject&MaxTimeoutsChecker $maxTimeoutsChecker;

    private MockObject&ConsoleOutput $consoleOutput;

    private MockObject&MetricsCalculator $metricsCalculator;

    private Stub&TestFrameworkExtraOptionsFilter $testFrameworkExtraOptionsFilter;

    protected function setUp(): void
    {
        $this->adapter = $this->createMock(TestFrameworkAdapter::class);
        $this->coverageChecker = $this->createMock(CoverageChecker::class);
        $this->eventDispatcher = $this->createMock(EventDispatcher::class);
        $this->initialTestsRunner = $this->createMock(InitialTestsRunner::class);
        $this->memoryLimiter = $this->createMock(MemoryLimiter::class);
        $this->mutationGenerator = $this->createMock(MutationGenerator::class);
        $this->mutationTestingRunner = $this->createMock(MutationTestingRunner::class);
        $this->minMsiChecker = $this->createMock(MinMsiChecker::class);
        $this->maxTimeoutsChecker = $this->createMock(MaxTimeoutsChecker::class);
        $this->consoleOutput = $this->createMock(ConsoleOutput::class);
        $this->metricsCalculator = $this->createMock(MetricsCalculator::class);
        $this->testFrameworkExtraOptionsFilter = $this->createStub(TestFrameworkExtraOptionsFilter::class);
    }

    public function test_initial_test_run_fails(): void
    {
        $config = ConfigurationBuilder::withMinimalTestData()
            ->withSkipInitialTests(false)
            ->build();

        $this->adapter
            ->expects($this->once())
            ->method('getName')
            ->willReturn('foo');
        $this->adapter
            ->expects($this->once())
            ->method('getInitialTestsFailRecommendations')
            ->willReturn('Run tests to see what failed');

        $process = $this->createInitialTestProcess(false, '');
        $process
            ->expects($this->once())
            ->method('getExitCode')
            ->willReturn(1);
        $process
            ->expects($this->atLeastOnce())
            ->method('getErrorOutput')
            ->willReturn('');

        $this->initialTestsRunner
            ->expects($this->once())
            ->method('run')
            ->willReturn($process);

        $this->coverageChecker->expects($this->never())->method($this->anything());
        $this->eventDispatcher->expects($this->never())->method($this->anything());
        $this->memoryLimiter->expects($this->never())->method($this->anything());
        $this->mutationGenerator->expects($this->never())->method($this->anything());
        $this->mutationTestingRunner->expects($this->never())->method($this->anything());
        $this->minMsiChecker->expects($this->never())->method($this->anything());
        $this->metricsCalculator->expects($this->never())->method($this->anything());
        $this->maxTimeoutsChecker->expects($this->never())->method($this->anything());

        $this->expectException(InitialTestsFailed::class);

        $this->createEngine($config)->execute();
    }

    public function test_initial_test_run_succeeds(): void
    {
        $config = ConfigurationBuilder::withMinimalTestData()
            ->withSkipInitialTests(false)
            ->withUncovered(true)
            ->build();

        $process = $this->createInitialTestProcess(true, 'testing');

        $this->initialTestsRunner
            ->expects($this->once())
            ->method('run')
            ->willReturn($process);

        $this->coverageChecker
            ->expects($this->once())
            ->method('checkCoverageHasBeenGenerated')
            ->with('/tmp/bar', 'testing');

        $this->memoryLimiter
            ->expects($this->once())
            ->method('limitMemory')
            ->with('testing', $this->adapter);

        $this->mutationGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(false)
            ->willReturn([]);

        $this->mutationTestingRunner
            ->expects($this->once())
            ->method('run')
            ->with([], '');

        $this->minMsiChecker
            ->expects($this->once())
            ->method('checkMetrics')
            ->with(1000, 2.0, 3.0, $this->consoleOutput);

        $this->metricsCalculator
            ->expects($this->once())
            ->method('getTestedMutantsCount')
            ->willReturn(1000);
        $this->metricsCalculator
            ->expects($this->once())
            ->method('getMutationScoreIndicator')
            ->willReturn(2.0);
        $this->metricsCalculator
            ->expects($this->once())
            ->method('getCoveredCodeMutationScoreIndicator')
            ->willReturn(3.0);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ApplicationExecutionWasFinished::class));

        $this->createEngine($config)->execute();
    }

    public function test_memory_limiter_is_applied_after_static_analysis_when_enabled(): void
    {
        $config = ConfigurationBuilder::withMinimalTestData()
            ->withSkipInitialTests(false)
            ->withStaticAnalysisTool(StaticAnalysisToolTypes::PHPSTAN)
            ->withUncovered(true)
            ->build();

        $callOrder = [];

        $this->initialTestsRunner
            ->expects($this->once())
            ->method('run')
            ->willReturn($this->createInitialTestProcess(true, 'test output'));

        $this->coverageChecker
            ->expects($this->once())
            ->method('checkCoverageHasBeenGenerated')
            ->with('/tmp/bar', 'test output');

        $staticAnalysisProcess = $this->createMock(Process::class);
        $staticAnalysisProcess
            ->expects($this->once())
            ->method('isSuccessful')
            ->willReturn(true);

        $initialStaticAnalysisRunner = $this->createMock(InitialStaticAnalysisRunner::class);
        $initialStaticAnalysisRunner
            ->expects($this->once())
            ->method('run')
            ->willReturn($staticAnalysisProcess);

        $staticAnalysisToolAdapter = $this->createStub(StaticAnalysisToolAdapter::class);

        $this->memoryLimiter
            ->expects($this->once())
            ->method('limitMemory')
            ->with('test output', $this->adapter)
            ->willReturnCallback(static function () use (&$callOrder): void {
                $callOrder[] = 'limitMemory';
            });

        $this->mutationGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(false)
            ->willReturnCallback(static function () use (&$callOrder): array {
                $callOrder[] = 'generate';

                return [];
            });

        $this->mutationTestingRunner
            ->expects($this->once())
            ->method('run')
            ->with([], '');

        $this->minMsiChecker
            ->expects($this->once())
            ->method('checkMetrics')
            ->with(100, 80.0, 85.0, $this->consoleOutput);

        $this->metricsCalculator
            ->method('getTestedMutantsCount')
            ->willReturn(100);
        $this->metricsCalculator
            ->method('getMutationScoreIndicator')
            ->willReturn(80.0);
        $this->metricsCalculator
            ->method('getCoveredCodeMutationScoreIndicator')
            ->willReturn(85.0);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ApplicationExecutionWasFinished::class));

        $engine = $this->createEngine(
            $config,
            $initialStaticAnalysisRunner,
            $staticAnalysisToolAdapter,
        );

        $engine->execute();

        $this->assertSame(['limitMemory', 'generate'], $callOrder);
    }

    public function test_memory_limiter_is_not_applied_when_initial_tests_are_skipped(): void
    {
        $config = ConfigurationBuilder::withMinimalTestData()
            ->withSkipInitialTests(true)
            ->withUncovered(true)
            ->build();

        $this->coverageChecker
            ->expects($this->once())
            ->method('checkCoverageExists');

        $this->consoleOutput
            ->expects($this->once())
            ->method('logSkippingInitialTests');

        $this->initialTestsRunner->expects($this->never())->method($this->anything());
        $this->memoryLimiter->expects($this->never())->method('limitMemory');

        $this->mutationGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(false)
            ->willReturn([]);

        $this->mutationTestingRunner
            ->expects($this->once())
            ->method('run')
            ->with([], '');

        $this->minMsiChecker
            ->expects($this->once())
            ->method('checkMetrics');

        $this->metricsCalculator
            ->expects($this->once())
            ->method('getTestedMutantsCount')
            ->willReturn(0);
        $this->metricsCalculator
            ->expects($this->once())
            ->method('getMutationScoreIndicator')
            ->willReturn(0.0);
        $this->metricsCalculator
            ->expects($this->once())
            ->method('getCoveredCodeMutationScoreIndicator')
            ->willReturn(0.0);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ApplicationExecutionWasFinished::class));

        $this->createEngine($config)->execute();
    }

    public function test_max_timeouts_checker_receives_correct_timed_out_count(): void
    {
        $config = ConfigurationBuilder::withMinimalTestData()
            ->withSkipInitialTests(true)
            ->withUncovered(true)
            ->build();

        $this->coverageChecker
            ->expects($this->once())
            ->method('checkCoverageExists');

        $this->consoleOutput
            ->expects($this->once())
            ->method('logSkippingInitialTests');

        $this->initialTestsRunner->expects($this->never())->method($this->anything());
        $this->memoryLimiter->expects($this->never())->method('limitMemory');

        $this->mutationGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(false)
            ->willReturn([]);

        $this->mutationTestingRunner
            ->expects($this->once())
            ->method('run')
            ->with([], '');

        $this->minMsiChecker->expects($this->once())->method('checkMetrics');

        $this->metricsCalculator
            ->expects($this->once())
            ->method('getTimedOutCount')
            ->willReturn(42);
        $this->metricsCalculator
            ->expects($this->once())
            ->method('getTestedMutantsCount')
            ->willReturn(0);
        $this->metricsCalculator
            ->expects($this->once())
            ->method('getMutationScoreIndicator')
            ->willReturn(0.0);
        $this->metricsCalculator
            ->expects($this->once())
            ->method('getCoveredCodeMutationScoreIndicator')
            ->willReturn(0.0);

        $this->maxTimeoutsChecker
            ->expects($this->once())
            ->method('checkTimeouts')
            ->with(42);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ApplicationExecutionWasFinished::class));

        $this->createEngine($config)->execute();
    }

    public function test_application_execution_was_finished_is_dispatched_when_max_timeouts_checker_throws(): void
    {
        $config = ConfigurationBuilder::withMinimalTestData()
            ->withSkipInitialTests(true)
            ->withUncovered(true)
            ->build();

        $this->coverageChecker
            ->expects($this->once())
            ->method('checkCoverageExists');

        $this->consoleOutput
            ->expects($this->once())
            ->method('logSkippingInitialTests');

        $this->initialTestsRunner->expects($this->never())->method($this->anything());
        $this->memoryLimiter->expects($this->never())->method('limitMemory');

        $this->mutationGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(false)
            ->willReturn([]);

        $this->mutationTestingRunner
            ->expects($this->once())
            ->method('run')
            ->with([], '');

        $this->minMsiChecker->expects($this->never())->method($this->anything());

        $this->metricsCalculator
            ->expects($this->once())
            ->method('getTimedOutCount')
            ->willReturn(100);

        $this->maxTimeoutsChecker
            ->expects($this->once())
            ->method('checkTimeouts')
            ->with(100)
            ->willThrowException(MaxTimeoutCountReached::create(10, 100));

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ApplicationExecutionWasFinished::class));

        $this->expectException(MaxTimeoutCountReached::class);

        $this->createEngine($config)->execute();
    }

    public function test_application_execution_was_finished_is_dispatched_when_min_msi_checker_throws(): void
    {
        $config = ConfigurationBuilder::withMinimalTestData()
            ->withSkipInitialTests(true)
            ->withUncovered(true)
            ->build();

        $this->coverageChecker
            ->expects($this->once())
            ->method('checkCoverageExists');

        $this->consoleOutput
            ->expects($this->once())
            ->method('logSkippingInitialTests');

        $this->initialTestsRunner->expects($this->never())->method($this->anything());
        $this->memoryLimiter->expects($this->never())->method('limitMemory');

        $this->mutationGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(false)
            ->willReturn([]);

        $this->mutationTestingRunner
            ->expects($this->once())
            ->method('run')
            ->with([], '');

        $this->minMsiChecker
            ->expects($this->once())
            ->method('checkMetrics')
            ->with(100, 50.0, 55.0, $this->consoleOutput)
            ->willThrowException(MinMsiCheckFailed::createForMsi(80.0, 50.0));

        $this->metricsCalculator
            ->expects($this->once())
            ->method('getTimedOutCount')
            ->willReturn(0);
        $this->metricsCalculator
            ->expects($this->once())
            ->method('getTestedMutantsCount')
            ->willReturn(100);
        $this->metricsCalculator
            ->expects($this->once())
            ->method('getMutationScoreIndicator')
            ->willReturn(50.0);
        $this->metricsCalculator
            ->expects($this->once())
            ->method('getCoveredCodeMutationScoreIndicator')
            ->willReturn(55.0);
        $this->maxTimeoutsChecker
            ->expects($this->once())
            ->method('checkTimeouts')
            ->with(0);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ApplicationExecutionWasFinished::class));

        $this->expectException(MinMsiCheckFailed::class);

        $this->createEngine($config)->execute();
    }

    private function createInitialTestProcess(bool $successful, string $output): MockObject&Process
    {
        $process = $this->createMock(Process::class);
        $process
            ->expects($this->once())
            ->method('isSuccessful')
            ->willReturn($successful);
        $process
            ->method('getCommandLine')
            ->willReturn('/tmp/bar');
        $process
            ->method('getOutput')
            ->willReturn($output);

        return $process;
    }

    private function createEngine(
        ?Configuration $config = null,
        ?InitialStaticAnalysisRunner $initialStaticAnalysisRunner = null,
        ?StaticAnalysisToolAdapter $staticAnalysisToolAdapter = null,
    ): Engine {
        return new Engine(
            config: $config ?? ConfigurationBuilder::withMinimalTestData()
                ->withSkipInitialTests(false)
                ->withUncovered(true)
                ->build(),
            adapter: $this->adapter,
            coverageChecker: $this->coverageChecker,
            eventDispatcher: $this->eventDispatcher,
            initialTestsRunner: $this->initialTestsRunner,
            memoryLimiter: $this->memoryLimiter,
            mutationGenerator: $this->mutationGenerator,
            mutationTestingRunner: $this->mutationTestingRunner,
            minMsiChecker: $this->minMsiChecker,
            maxTimeoutsChecker: $this->maxTimeoutsChecker,
            consoleOutput: $this->consoleOutput,
            metricsCalculator: $this->metricsCalculator,
            testFrameworkExtraOptionsFilter: $this->testFrameworkExtraOptionsFilter,
            initialStaticAnalysisRunner: $initialStaticAnalysisRunner,
            staticAnalysisToolAdapter: $staticAnalysisToolAdapter,
        );
    }
}
