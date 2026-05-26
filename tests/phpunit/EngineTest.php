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
use Infection\Console\ConsoleOutput;
use Infection\Engine;
use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\Events\Application\ApplicationExecutionWasFinished;
use Infection\Metrics\Calculator;
use Infection\Metrics\MaxTimeoutCountReached;
use Infection\Metrics\MaxTimeoutsChecker;
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\MinMsiChecker;
use Infection\Metrics\MinMsiCheckFailed;
use Infection\Mutant\DetectionStatus;
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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Process\Process;

#[CoversClass(Engine::class)]
final class EngineTest extends TestCase
{
    public function test_initial_test_run_fails(): void
    {
        $config = ConfigurationBuilder::withMinimalTestData()
            ->withSkipInitialTests(false)
            ->build();

        $adapter = $this->createMock(TestFrameworkAdapter::class);
        $adapter
            ->expects($this->once())
            ->method('getName')
            ->willReturn('foo')
        ;
        $adapter
            ->expects($this->once())
            ->method('getInitialTestsFailRecommendations')
            ->willReturn('Run tests to see what failed')
        ;

        $coverageChecker = $this->createMock(CoverageChecker::class);
        $coverageChecker->expects($this->never())->method($this->anything());

        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher->expects($this->never())->method($this->anything());

        $process = $this->createMock(Process::class);
        $process
            ->expects($this->once())
            ->method('isSuccessful')
            ->willReturn(false)
        ;

        $process
            ->expects($this->once())
            ->method('getCommandLine')
            ->willReturn('/tmp/bar')
        ;

        $process
            ->expects($this->once())
            ->method('getExitCode')
            ->willReturn(1)
        ;

        $process
            ->expects($this->atLeastOnce())
            ->method('getOutput')
            ->willReturn('')
        ;

        $process
            ->expects($this->atLeastOnce())
            ->method('getErrorOutput')
            ->willReturn('')
        ;

        $initialTestsRunner = $this->createMock(InitialTestsRunner::class);
        $initialTestsRunner
            ->expects($this->once())
            ->method('run')
            ->willReturn($process)
        ;

        $memoryLimiter = $this->createMock(MemoryLimiter::class);
        $memoryLimiter->expects($this->never())->method($this->anything());

        $mutationGenerator = $this->createMock(MutationGenerator::class);
        $mutationGenerator->expects($this->never())->method($this->anything());

        $mutationTestingRunner = $this->createMock(MutationTestingRunner::class);
        $mutationTestingRunner->expects($this->never())->method($this->anything());

        $minMsiChecker = $this->createMock(MinMsiChecker::class);
        $minMsiChecker->expects($this->never())->method($this->anything());

        $consoleOutput = $this->createMock(ConsoleOutput::class);
        $consoleOutput->expects($this->never())->method($this->anything());

        $metricsCalculator = new MetricsCalculator(0);

        $testFrameworkExtraOptionsFilter = $this->createMock(TestFrameworkExtraOptionsFilter::class);
        $testFrameworkExtraOptionsFilter->expects($this->never())->method($this->anything());

        $maxTimeoutsChecker = $this->createMock(MaxTimeoutsChecker::class);
        $maxTimeoutsChecker->expects($this->never())->method($this->anything());

        $engine = new Engine(
            $config,
            $adapter,
            $coverageChecker,
            $eventDispatcher,
            $initialTestsRunner,
            $memoryLimiter,
            $mutationGenerator,
            $mutationTestingRunner,
            $minMsiChecker,
            $maxTimeoutsChecker,
            $consoleOutput,
            $metricsCalculator,
            $testFrameworkExtraOptionsFilter,
        );

        $this->expectException(InitialTestsFailed::class);

        $engine->execute();
    }

    public function test_initial_test_run_succeeds(): void
    {
        $config = ConfigurationBuilder::withMinimalTestData()
            ->withSkipInitialTests(false)
            ->withUncovered(true)
            ->build();

        $adapter = $this->createMock(TestFrameworkAdapter::class);
        $adapter->expects($this->never())->method($this->anything());

        $coverageChecker = $this->createMock(CoverageChecker::class);
        $coverageChecker
            ->expects($this->once())
            ->method('checkCoverageHasBeenGenerated')
        ;

        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(static fn (ApplicationExecutionWasFinished $event): bool => true));

        $process = $this->createMock(Process::class);
        $process
            ->expects($this->once())
            ->method('isSuccessful')
            ->willReturn(true)
        ;

        $process
            ->expects($this->once())
            ->method('getCommandLine')
            ->willReturn('/tmp/bar')
        ;

        $process
            ->expects($this->exactly(2))
            ->method('getOutput')
            ->willReturn('testing')
        ;

        $initialTestsRunner = $this->createMock(InitialTestsRunner::class);
        $initialTestsRunner
            ->expects($this->once())
            ->method('run')
            ->willReturn($process)
        ;

        $memoryLimiter = $this->createMock(MemoryLimiter::class);
        $memoryLimiter
            ->expects($this->once())
            ->method('limitMemory')
            ->with('testing', $adapter)
        ;

        $mutationGenerator = $this->createMock(MutationGenerator::class);
        $mutationGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(false)
        ;

        $mutationTestingRunner = $this->createMock(MutationTestingRunner::class);
        $mutationTestingRunner
            ->expects($this->once())
            ->method('run')
            ->with($this->callback(static fn (iterable $input): bool => true))
        ;

        $consoleOutput = $this->createMock(ConsoleOutput::class);
        $consoleOutput->expects($this->never())->method($this->anything());

        $minMsiChecker = $this->createMock(MinMsiChecker::class);
        $minMsiChecker
            ->expects($this->once())
            ->method('checkMetrics')
            ->with(1000, 2.0, 3.0, $consoleOutput)
        ;

        $metricsCalculator = self::createMetricsCalculator(
            testedMutantsCount: 1000,
            mutationScoreIndicator: 2.0,
            coveredCodeMutationScoreIndicator: 3.0,
        );

        $testFrameworkExtraOptionsFilter = $this->createMock(TestFrameworkExtraOptionsFilter::class);
        $testFrameworkExtraOptionsFilter->expects($this->never())->method($this->anything());

        $maxTimeoutsChecker = $this->createStub(MaxTimeoutsChecker::class);

        $engine = new Engine(
            $config,
            $adapter,
            $coverageChecker,
            $eventDispatcher,
            $initialTestsRunner,
            $memoryLimiter,
            $mutationGenerator,
            $mutationTestingRunner,
            $minMsiChecker,
            $maxTimeoutsChecker,
            $consoleOutput,
            $metricsCalculator,
            $testFrameworkExtraOptionsFilter,
        );

        $engine->execute();
    }

    public function test_memory_limiter_is_applied_after_static_analysis_when_enabled(): void
    {
        $config = ConfigurationBuilder::withMinimalTestData()
            ->withSkipInitialTests(false)
            ->withStaticAnalysisTool(StaticAnalysisToolTypes::PHPSTAN)
            ->withUncovered(true)
            ->build();

        $adapter = $this->createStub(TestFrameworkAdapter::class);

        $coverageChecker = $this->createMock(CoverageChecker::class);
        $coverageChecker
            ->expects($this->once())
            ->method('checkCoverageHasBeenGenerated')
        ;

        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(static fn (ApplicationExecutionWasFinished $event): bool => true));

        $initialTestProcess = $this->createMock(Process::class);
        $initialTestProcess
            ->expects($this->once())
            ->method('isSuccessful')
            ->willReturn(true)
        ;
        $initialTestProcess
            ->expects($this->once())
            ->method('getCommandLine')
            ->willReturn('/tmp/bar')
        ;
        $initialTestProcess
            ->expects($this->exactly(2))
            ->method('getOutput')
            ->willReturn('test output')
        ;

        $staticAnalysisProcess = $this->createMock(Process::class);
        $staticAnalysisProcess
            ->expects($this->once())
            ->method('isSuccessful')
            ->willReturn(true)
        ;

        $initialTestsRunner = $this->createMock(InitialTestsRunner::class);
        $initialTestsRunner
            ->expects($this->once())
            ->method('run')
            ->willReturn($initialTestProcess)
        ;

        $initialStaticAnalysisRunner = $this->createMock(InitialStaticAnalysisRunner::class);
        $initialStaticAnalysisRunner
            ->expects($this->once())
            ->method('run')
            ->willReturn($staticAnalysisProcess)
        ;

        $staticAnalysisToolAdapter = $this->createStub(StaticAnalysisToolAdapter::class);

        $callOrder = [];

        $memoryLimiter = $this->createMock(MemoryLimiter::class);
        $memoryLimiter
            ->expects($this->once())
            ->method('limitMemory')
            ->with('test output', $adapter)
            ->willReturnCallback(static function () use (&$callOrder): void {
                $callOrder[] = 'limitMemory';
            })
        ;

        $mutationGenerator = $this->createMock(MutationGenerator::class);
        $mutationGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(false)
            ->willReturnCallback(static function () use (&$callOrder) {
                $callOrder[] = 'generate';

                return [];
            })
        ;

        $mutationTestingRunner = $this->createMock(MutationTestingRunner::class);
        $mutationTestingRunner
            ->expects($this->once())
            ->method('run')
            ->with($this->callback(static fn (iterable $input): bool => true))
        ;

        $consoleOutput = $this->createStub(ConsoleOutput::class);

        $minMsiChecker = $this->createMock(MinMsiChecker::class);
        $minMsiChecker
            ->expects($this->once())
            ->method('checkMetrics')
            ->with(100, 80.0, 85.0, $consoleOutput)
        ;

        $metricsCalculator = self::createMetricsCalculator(
            testedMutantsCount: 100,
            mutationScoreIndicator: 80.0,
            coveredCodeMutationScoreIndicator: 85.0,
        );

        $testFrameworkExtraOptionsFilter = $this->createStub(TestFrameworkExtraOptionsFilter::class);

        $maxTimeoutsChecker = $this->createStub(MaxTimeoutsChecker::class);

        $engine = new Engine(
            $config,
            $adapter,
            $coverageChecker,
            $eventDispatcher,
            $initialTestsRunner,
            $memoryLimiter,
            $mutationGenerator,
            $mutationTestingRunner,
            $minMsiChecker,
            $maxTimeoutsChecker,
            $consoleOutput,
            $metricsCalculator,
            $testFrameworkExtraOptionsFilter,
            $initialStaticAnalysisRunner,
            $staticAnalysisToolAdapter,
        );

        $engine->execute();

        // Verify that limitMemory is called before mutation generation
        $this->assertSame(['limitMemory', 'generate'], $callOrder);
    }

    public function test_memory_limiter_is_not_applied_when_initial_tests_are_skipped(): void
    {
        $config = ConfigurationBuilder::withMinimalTestData()
            ->withSkipInitialTests(true)
            ->withUncovered(true)
            ->build();

        $adapter = $this->createStub(TestFrameworkAdapter::class);

        $coverageChecker = $this->createMock(CoverageChecker::class);
        $coverageChecker
            ->expects($this->once())
            ->method('checkCoverageExists')
        ;

        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(static fn (ApplicationExecutionWasFinished $event): bool => true));

        $initialTestsRunner = $this->createMock(InitialTestsRunner::class);
        $initialTestsRunner->expects($this->never())->method($this->anything());

        $memoryLimiter = $this->createMock(MemoryLimiter::class);
        $memoryLimiter->expects($this->never())->method('limitMemory');

        $mutationGenerator = $this->createMock(MutationGenerator::class);
        $mutationGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(false)
            ->willReturn([])
        ;

        $mutationTestingRunner = $this->createMock(MutationTestingRunner::class);
        $mutationTestingRunner
            ->expects($this->once())
            ->method('run')
            ->with($this->callback(static fn (iterable $input): bool => true))
        ;

        $consoleOutput = $this->createMock(ConsoleOutput::class);
        $consoleOutput
            ->expects($this->once())
            ->method('logSkippingInitialTests')
        ;

        $minMsiChecker = $this->createMock(MinMsiChecker::class);
        $minMsiChecker
            ->expects($this->once())
            ->method('checkMetrics')
        ;

        $metricsCalculator = self::createMetricsCalculator(
            testedMutantsCount: 0,
            mutationScoreIndicator: 0.0,
            coveredCodeMutationScoreIndicator: 0.0,
        );

        $testFrameworkExtraOptionsFilter = $this->createStub(TestFrameworkExtraOptionsFilter::class);

        $maxTimeoutsChecker = $this->createStub(MaxTimeoutsChecker::class);

        $engine = new Engine(
            $config,
            $adapter,
            $coverageChecker,
            $eventDispatcher,
            $initialTestsRunner,
            $memoryLimiter,
            $mutationGenerator,
            $mutationTestingRunner,
            $minMsiChecker,
            $maxTimeoutsChecker,
            $consoleOutput,
            $metricsCalculator,
            $testFrameworkExtraOptionsFilter,
        );

        $engine->execute();
    }

    public function test_max_timeouts_checker_receives_correct_timed_out_count(): void
    {
        $config = ConfigurationBuilder::withMinimalTestData()
            ->withSkipInitialTests(true)
            ->withUncovered(true)
            ->build();

        $adapter = $this->createStub(TestFrameworkAdapter::class);

        $coverageChecker = $this->createMock(CoverageChecker::class);
        $coverageChecker
            ->expects($this->once())
            ->method('checkCoverageExists')
        ;

        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(static fn (ApplicationExecutionWasFinished $event): bool => true));

        $initialTestsRunner = $this->createMock(InitialTestsRunner::class);
        $initialTestsRunner->expects($this->never())->method($this->anything());

        $memoryLimiter = $this->createMock(MemoryLimiter::class);
        $memoryLimiter->expects($this->never())->method('limitMemory');

        $mutationGenerator = $this->createMock(MutationGenerator::class);
        $mutationGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(false)
            ->willReturn([])
        ;

        $mutationTestingRunner = $this->createMock(MutationTestingRunner::class);
        $mutationTestingRunner
            ->expects($this->once())
            ->method('run')
            ->with($this->callback(static fn (iterable $input): bool => true))
        ;

        $consoleOutput = $this->createMock(ConsoleOutput::class);
        $consoleOutput
            ->expects($this->once())
            ->method('logSkippingInitialTests')
        ;

        $minMsiChecker = $this->createMock(MinMsiChecker::class);
        $minMsiChecker
            ->expects($this->once())
            ->method('checkMetrics')
        ;

        $metricsCalculator = self::createMetricsCalculator(
            testedMutantsCount: 0,
            mutationScoreIndicator: 0.0,
            coveredCodeMutationScoreIndicator: 0.0,
            timedOutCount: 42,
        );

        $testFrameworkExtraOptionsFilter = $this->createStub(TestFrameworkExtraOptionsFilter::class);

        $maxTimeoutsChecker = $this->createMock(MaxTimeoutsChecker::class);
        $maxTimeoutsChecker
            ->expects($this->once())
            ->method('checkTimeouts')
            ->with(42)
        ;

        $engine = new Engine(
            $config,
            $adapter,
            $coverageChecker,
            $eventDispatcher,
            $initialTestsRunner,
            $memoryLimiter,
            $mutationGenerator,
            $mutationTestingRunner,
            $minMsiChecker,
            $maxTimeoutsChecker,
            $consoleOutput,
            $metricsCalculator,
            $testFrameworkExtraOptionsFilter,
        );

        $engine->execute();
    }

    public function test_application_execution_was_finished_is_dispatched_when_max_timeouts_checker_throws(): void
    {
        $config = ConfigurationBuilder::withMinimalTestData()
            ->withSkipInitialTests(true)
            ->withUncovered(true)
            ->build();

        $adapter = $this->createStub(TestFrameworkAdapter::class);

        $coverageChecker = $this->createMock(CoverageChecker::class);
        $coverageChecker
            ->expects($this->once())
            ->method('checkCoverageExists')
        ;

        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(static fn (ApplicationExecutionWasFinished $event): bool => true));

        $initialTestsRunner = $this->createMock(InitialTestsRunner::class);
        $initialTestsRunner->expects($this->never())->method($this->anything());

        $memoryLimiter = $this->createMock(MemoryLimiter::class);
        $memoryLimiter->expects($this->never())->method('limitMemory');

        $mutationGenerator = $this->createMock(MutationGenerator::class);
        $mutationGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(false)
            ->willReturn([])
        ;

        $mutationTestingRunner = $this->createMock(MutationTestingRunner::class);
        $mutationTestingRunner
            ->expects($this->once())
            ->method('run')
            ->with($this->callback(static fn (iterable $input): bool => true))
        ;

        $consoleOutput = $this->createMock(ConsoleOutput::class);
        $consoleOutput
            ->expects($this->once())
            ->method('logSkippingInitialTests')
        ;

        $minMsiChecker = $this->createMock(MinMsiChecker::class);
        $minMsiChecker->expects($this->never())->method($this->anything());

        $metricsCalculator = self::createMetricsCalculator(timedOutCount: 100);

        $testFrameworkExtraOptionsFilter = $this->createStub(TestFrameworkExtraOptionsFilter::class);

        $maxTimeoutsChecker = $this->createMock(MaxTimeoutsChecker::class);
        $maxTimeoutsChecker
            ->expects($this->once())
            ->method('checkTimeouts')
            ->with(100)
            ->willThrowException(MaxTimeoutCountReached::create(10, 100))
        ;

        $engine = new Engine(
            $config,
            $adapter,
            $coverageChecker,
            $eventDispatcher,
            $initialTestsRunner,
            $memoryLimiter,
            $mutationGenerator,
            $mutationTestingRunner,
            $minMsiChecker,
            $maxTimeoutsChecker,
            $consoleOutput,
            $metricsCalculator,
            $testFrameworkExtraOptionsFilter,
        );

        $this->expectException(MaxTimeoutCountReached::class);

        $engine->execute();
    }

    public function test_application_execution_was_finished_is_dispatched_when_min_msi_checker_throws(): void
    {
        $config = ConfigurationBuilder::withMinimalTestData()
            ->withSkipInitialTests(true)
            ->withUncovered(true)
            ->build();

        $adapter = $this->createStub(TestFrameworkAdapter::class);

        $coverageChecker = $this->createMock(CoverageChecker::class);
        $coverageChecker
            ->expects($this->once())
            ->method('checkCoverageExists')
        ;

        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(static fn (ApplicationExecutionWasFinished $event): bool => true));

        $initialTestsRunner = $this->createMock(InitialTestsRunner::class);
        $initialTestsRunner->expects($this->never())->method($this->anything());

        $memoryLimiter = $this->createMock(MemoryLimiter::class);
        $memoryLimiter->expects($this->never())->method('limitMemory');

        $mutationGenerator = $this->createMock(MutationGenerator::class);
        $mutationGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(false)
            ->willReturn([])
        ;

        $mutationTestingRunner = $this->createMock(MutationTestingRunner::class);
        $mutationTestingRunner
            ->expects($this->once())
            ->method('run')
            ->with($this->callback(static fn (iterable $input): bool => true))
        ;

        $consoleOutput = $this->createMock(ConsoleOutput::class);
        $consoleOutput
            ->expects($this->once())
            ->method('logSkippingInitialTests')
        ;

        $minMsiChecker = $this->createMock(MinMsiChecker::class);
        $minMsiChecker
            ->expects($this->once())
            ->method('checkMetrics')
            ->with(100, 50.0, 55.0, $consoleOutput)
            ->willThrowException(MinMsiCheckFailed::createForMsi(80.0, 50.0))
        ;

        $metricsCalculator = self::createMetricsCalculator(
            testedMutantsCount: 100,
            mutationScoreIndicator: 50.0,
            coveredCodeMutationScoreIndicator: 55.0,
        );

        $testFrameworkExtraOptionsFilter = $this->createStub(TestFrameworkExtraOptionsFilter::class);

        $maxTimeoutsChecker = $this->createMock(MaxTimeoutsChecker::class);
        $maxTimeoutsChecker
            ->expects($this->once())
            ->method('checkTimeouts')
            ->with(0)
        ;

        $engine = new Engine(
            $config,
            $adapter,
            $coverageChecker,
            $eventDispatcher,
            $initialTestsRunner,
            $memoryLimiter,
            $mutationGenerator,
            $mutationTestingRunner,
            $minMsiChecker,
            $maxTimeoutsChecker,
            $consoleOutput,
            $metricsCalculator,
            $testFrameworkExtraOptionsFilter,
        );

        $this->expectException(MinMsiCheckFailed::class);

        $engine->execute();
    }

    private static function createMetricsCalculator(
        int $testedMutantsCount = 0,
        float $mutationScoreIndicator = 0.0,
        float $coveredCodeMutationScoreIndicator = 0.0,
        int $timedOutCount = 0,
    ): MetricsCalculator {
        $metricsCalculator = new MetricsCalculator(0);

        $reflection = new ReflectionClass($metricsCalculator);
        $countByStatus = $reflection->getProperty('countByStatus')->getValue($metricsCalculator);
        $countByStatus[DetectionStatus::TIMED_OUT->value] = $timedOutCount;

        $reflection->getProperty('countByStatus')->setValue($metricsCalculator, $countByStatus);
        $reflection->getProperty('totalMutantsCount')->setValue($metricsCalculator, $testedMutantsCount);
        $reflection->getProperty('calculator')->setValue(
            $metricsCalculator,
            self::createCalculator($mutationScoreIndicator, $coveredCodeMutationScoreIndicator),
        );

        return $metricsCalculator;
    }

    private static function createCalculator(
        float $mutationScoreIndicator,
        float $coveredCodeMutationScoreIndicator,
    ): Calculator {
        $calculator = new Calculator(0, 0, 0, 0, 0, 0);

        $reflection = new ReflectionClass($calculator);
        $reflection->getProperty('mutationScoreIndicator')->setValue($calculator, $mutationScoreIndicator);
        $reflection->getProperty('coveredMutationScoreIndicator')->setValue(
            $calculator,
            $coveredCodeMutationScoreIndicator,
        );

        return $calculator;
    }
}
