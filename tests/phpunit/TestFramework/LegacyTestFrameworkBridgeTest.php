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

use Closure;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Console\ConsoleOutput;
use Infection\Process\Factory\InitialTestsRunProcessFactory;
use Infection\Process\Factory\MutantProcessContainerFactory;
use Infection\Process\MutantProcessContainer;
use Infection\TestFramework\Common\Throwable\InitialTestsFailed;
use Infection\TestFramework\Contracts\InitialRunResults;
use Infection\TestFramework\Coverage\CoverageChecker;
use Infection\TestFramework\LegacyTestFrameworkBridge;
use Infection\TestFramework\ProvidesInitialRunOnlyOptions;
use Infection\TestFramework\TestFrameworkExtraOptionsFilter;
use Infection\Tests\Configuration\ConfigurationBuilder;
use Infection\Tests\Fixtures\TestFramework\DummyTestFrameworkAdapter;
use Infection\Tests\Fixtures\TestFramework\FakeAwareAdapter;
use Infection\Tests\Mutant\MutantBuilder;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(LegacyTestFrameworkBridge::class)]
final class LegacyTestFrameworkBridgeTest extends TestCase
{
    public function test_it_exposes_the_adapter_name(): void
    {
        $testFramework = $this->createTestFramework(
            adapter: new DummyTestFrameworkAdapter(),
        );

        $actual = $testFramework->getName();

        $this->assertSame('dummy', $actual);
    }

    public function test_it_exposes_the_adapter_version(): void
    {
        $testFramework = $this->createTestFramework(
            adapter: new DummyTestFrameworkAdapter(),
        );

        $actual = $testFramework->getVersion();

        $this->assertSame('1.0.0', $actual);
    }

    public function test_it_checks_existing_coverage_when_initial_tests_are_skipped(): void
    {
        $consoleOutput = $this->createMock(ConsoleOutput::class);
        $consoleOutput
            ->expects($this->once())
            ->method('logSkippingInitialTests')
        ;

        $coverageChecker = $this->createMock(CoverageChecker::class);
        $coverageChecker
            ->expects($this->once())
            ->method('checkCoverageExists')
        ;

        $testFramework = $this->createTestFramework(
            consoleOutput: $consoleOutput,
            coverageChecker: $coverageChecker,
            skipInitialTests: true,
        );

        $testFramework->checkRequirements();
    }

    public function test_it_has_not_requirements_when_the_initial_tests_are_run(): void
    {
        $consoleOutput = $this->createMock(ConsoleOutput::class);
        $consoleOutput
            ->expects($this->never())
            ->method('logSkippingInitialTests')
        ;

        $coverageChecker = $this->createMock(CoverageChecker::class);
        $coverageChecker
            ->expects($this->never())
            ->method('checkCoverageExists')
        ;

        $testFramework = $this->createTestFramework(
            consoleOutput: $consoleOutput,
            coverageChecker: $coverageChecker,
        );

        $testFramework->checkRequirements();
    }

    public function test_it_executes_initial_run_and_reports_memory_usage(): void
    {
        $startedCommands = [];
        $onStart = static function (string $commandLine) use (&$startedCommands): void {
            $startedCommands[] = $commandLine;
        };
        $process = $this->createSuccessfulInitialRunProcess('output');
        $process
            ->expects($this->once())
            ->method('run')
            ->with($this->isInstanceOf(Closure::class))
            ->willReturnCallback(function () use (&$startedCommands): int {
                $this->assertSame(['/tmp/phpunit'], $startedCommands);

                return 0;
            })
        ;
        $adapter = new FakeAwareAdapter(42.0);

        $initialRunProcessFactory = $this->createMock(InitialTestsRunProcessFactory::class);
        $initialRunProcessFactory
            ->expects($this->once())
            ->method('createProcess')
            ->with($adapter, '', [''], false)
            ->willReturn($process)
        ;

        $coverageChecker = $this->createMock(CoverageChecker::class);
        $coverageChecker
            ->expects($this->once())
            ->method('checkCoverageHasBeenGenerated')
            ->with('/tmp/phpunit', 'output')
        ;

        $testFramework = $this->createTestFramework(
            adapter: $adapter,
            coverageChecker: $coverageChecker,
            initialRunProcessFactory: $initialRunProcessFactory,
        );

        $expected = new InitialRunResults(
            output: 'output',
            memoryUsage: 42.0,
        );

        $actual = $testFramework->executeInitialRun(onStart: $onStart);

        $this->assertEquals($expected, $actual);
    }

    public function test_it_forwards_initial_run_options(): void
    {
        $process = $this->createSuccessfulInitialRunProcess('output');
        $adapter = new DummyTestFrameworkAdapter();

        $initialRunProcessFactory = $this->createMock(InitialTestsRunProcessFactory::class);
        $initialRunProcessFactory
            ->expects($this->once())
            ->method('createProcess')
            ->with($adapter, '--verbose', ['-d', 'memory_limit=1G'], true)
            ->willReturn($process)
        ;

        $testFramework = $this->createTestFramework(
            adapter: $adapter,
            initialRunProcessFactory: $initialRunProcessFactory,
            initialTestsPhpOptions: '-d memory_limit=1G',
            testFrameworkExtraOptions: '--verbose',
            skipCoverage: true,
        );

        $testFramework->executeInitialRun();
    }

    public function test_it_throws_when_the_initial_run_fails(): void
    {
        $adapter = $this->createMock(TestFrameworkAdapter::class);
        $adapter
            ->expects($this->once())
            ->method('getName')
            ->willReturn('LegacyFramework')
        ;
        $adapter
            ->expects($this->once())
            ->method('getInitialTestsFailRecommendations')
            ->with('/tmp/phpunit')
            ->willReturn('Check the legacy framework configuration.')
        ;

        $initialRunProcessFactory = $this->createMock(InitialTestsRunProcessFactory::class);
        $initialRunProcessFactory
            ->expects($this->once())
            ->method('createProcess')
            ->willReturn($this->createFailedInitialRunProcess())
        ;

        $coverageChecker = $this->createMock(CoverageChecker::class);
        $coverageChecker
            ->expects($this->never())
            ->method('checkCoverageHasBeenGenerated')
        ;

        $testFramework = $this->createTestFramework(
            adapter: $adapter,
            coverageChecker: $coverageChecker,
            initialRunProcessFactory: $initialRunProcessFactory,
        );

        $this->expectException(InitialTestsFailed::class);
        $this->expectExceptionMessage(
            <<<'MESSAGE'
                Check the legacy framework configuration.
                LegacyFramework reported an exit code of 1.
                MESSAGE,
        );

        $testFramework->executeInitialRun();
    }

    public function test_it_reports_unknown_memory_usage_when_the_legacy_adapter_does_not_report_it(): void
    {
        $initialRunProcessFactory = $this->createMock(InitialTestsRunProcessFactory::class);
        $initialRunProcessFactory
            ->method('createProcess')
            ->willReturn($this->createSuccessfulInitialRunProcess('output'))
        ;

        $testFramework = $this->createTestFramework(
            adapter: new DummyTestFrameworkAdapter(),
            initialRunProcessFactory: $initialRunProcessFactory,
        );

        $expected = new InitialRunResults(
            output: 'output',
            memoryUsage: null,
        );

        $actual = $testFramework->executeInitialRun();

        $this->assertEquals($expected, $actual);
    }

    public function test_it_normalizes_unknown_legacy_memory_usage(): void
    {
        $initialRunProcessFactory = $this->createMock(InitialTestsRunProcessFactory::class);
        $initialRunProcessFactory
            ->method('createProcess')
            ->willReturn($this->createSuccessfulInitialRunProcess('output'))
        ;

        $testFramework = $this->createTestFramework(
            adapter: new FakeAwareAdapter(-1.0),
            initialRunProcessFactory: $initialRunProcessFactory,
        );

        $expected = new InitialRunResults(
            output: 'output',
            memoryUsage: null,
        );

        $actual = $testFramework->executeInitialRun();

        $this->assertEquals($expected, $actual);
    }

    public function test_it_delegates_mutant_evaluation_to_the_legacy_process_factory(): void
    {
        $mutant = MutantBuilder::withMinimalTestData()->build();
        $processContainer = $this->createStub(MutantProcessContainer::class);
        $adapter = new DummyTestFrameworkAdapter();

        $processFactoryMock = $this->createMock(MutantProcessContainerFactory::class);
        $processFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($adapter, $mutant, '')
            ->willReturn($processContainer)
        ;

        $testFramework = $this->createTestFramework(adapter: $adapter, processFactory: $processFactoryMock);

        $actual = $testFramework->test($mutant);

        $this->assertSame($processContainer, $actual);
    }

    public function test_it_filters_initial_run_only_options_from_mutant_evaluation(): void
    {
        $mutant = MutantBuilder::withMinimalTestData()->build();
        $processContainer = $this->createStub(MutantProcessContainer::class);
        $adapter = $this->createInitialRunOnlyOptionsAdapter();

        $processFactoryMock = $this->createMock(MutantProcessContainerFactory::class);
        $processFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($adapter, $mutant, '--filter FooTest')
            ->willReturn($processContainer)
        ;

        $testFrameworkExtraOptionsFilter = $this->createMock(TestFrameworkExtraOptionsFilter::class);
        $testFrameworkExtraOptionsFilter
            ->expects($this->once())
            ->method('filterForMutantProcess')
            ->with('--configuration phpunit.xml --filter FooTest', ['--configuration'])
            ->willReturn('--filter FooTest')
        ;

        $testFramework = $this->createTestFramework(
            adapter: $adapter,
            processFactory: $processFactoryMock,
            testFrameworkExtraOptionsFilter: $testFrameworkExtraOptionsFilter,
            testFrameworkExtraOptions: '--configuration phpunit.xml --filter FooTest',
        );

        $actual = $testFramework->test($mutant);

        $this->assertSame($processContainer, $actual);
    }

    #[DataProvider('progressOutputProvider')]
    public function test_it_reports_progress_and_stops_on_stderr(string $outputType, bool $reportProgress): void
    {
        $progressCount = 0;
        $stopCount = 0;
        $onProgress = static function () use (&$progressCount): void {
            ++$progressCount;
        };
        $process = $this->createMock(Process::class);
        $process->method('isSuccessful')->willReturn(true);
        $process
            ->method('stop')
            ->willReturnCallback(static function () use (&$stopCount): int {
                ++$stopCount;

                return 0;
            })
        ;
        $process
            ->expects($this->once())
            ->method('run')
            ->willReturnCallback(static function (Closure $callback) use ($outputType): int {
                $callback($outputType, 'output');

                return 0;
            })
        ;
        $factory = $this->createStub(InitialTestsRunProcessFactory::class);
        $factory->method('createProcess')->willReturn($process);
        $framework = $this->createTestFramework(initialRunProcessFactory: $factory);

        $framework->executeInitialRun(
            $reportProgress
                ? $onProgress
                : null,
        );

        $this->assertSame(
            $reportProgress
                ? 1
                : 0,
            $progressCount,
        );
        $this->assertSame(
            $outputType === Process::ERR
                ? 1
                : 0,
            $stopCount,
        );
    }

    public static function progressOutputProvider(): iterable
    {
        yield 'stdout with progress callback' => [Process::OUT, true];

        yield 'stderr with progress callback' => [Process::ERR, true];

        yield 'stdout without progress callback' => [Process::OUT, false];
    }

    private function createTestFramework(
        ?TestFrameworkAdapter $adapter = null,
        ?ConsoleOutput $consoleOutput = null,
        ?CoverageChecker $coverageChecker = null,
        ?InitialTestsRunProcessFactory $initialRunProcessFactory = null,
        ?MutantProcessContainerFactory $processFactory = null,
        ?TestFrameworkExtraOptionsFilter $testFrameworkExtraOptionsFilter = null,
        bool $skipInitialTests = false,
        ?string $initialTestsPhpOptions = null,
        string $testFrameworkExtraOptions = '',
        bool $skipCoverage = false,
    ): LegacyTestFrameworkBridge {
        return new LegacyTestFrameworkBridge(
            adapter: $adapter ?? new FakeAwareAdapter(1.0),
            consoleOutput: $consoleOutput ?? $this->createStub(ConsoleOutput::class),
            coverageChecker: $coverageChecker ?? $this->createStub(CoverageChecker::class),
            initialRunProcessFactory: $initialRunProcessFactory ?? $this->createStub(InitialTestsRunProcessFactory::class),
            config: ConfigurationBuilder::withMinimalTestData()
                ->withInitialTestsPhpOptions($initialTestsPhpOptions)
                ->withTestFrameworkExtraOptions($testFrameworkExtraOptions)
                ->withSkipCoverage($skipCoverage)
                ->withSkipInitialTests($skipInitialTests)
                ->build(),
            processFactory: $processFactory ?? $this->createStub(MutantProcessContainerFactory::class),
            testFrameworkExtraOptionsFilter: $testFrameworkExtraOptionsFilter ?? $this->createStub(TestFrameworkExtraOptionsFilter::class),
        );
    }

    private function createSuccessfulInitialRunProcess(string $output): Process&MockObject
    {
        $process = $this->createMock(Process::class);
        $process
            ->expects($this->once())
            ->method('isSuccessful')
            ->willReturn(true)
        ;
        $process
            ->method('getOutput')
            ->willReturn($output)
        ;
        $process
            ->method('getCommandLine')
            ->willReturn('/tmp/phpunit')
        ;

        return $process;
    }

    private function createFailedInitialRunProcess(): Process
    {
        $process = $this->createMock(Process::class);
        $process
            ->expects($this->once())
            ->method('isSuccessful')
            ->willReturn(false)
        ;
        $process
            ->method('getExitCode')
            ->willReturn(1)
        ;
        $process
            ->method('getCommandLine')
            ->willReturn('/tmp/phpunit')
        ;
        $process
            ->method('getOutput')
            ->willReturn('output')
        ;
        $process
            ->method('getErrorOutput')
            ->willReturn('error')
        ;

        return $process;
    }

    private function createInitialRunOnlyOptionsAdapter(): TestFrameworkAdapter&ProvidesInitialRunOnlyOptions
    {
        $adapter = $this->createMockForIntersectionOfInterfaces([
            TestFrameworkAdapter::class,
            ProvidesInitialRunOnlyOptions::class,
        ]);
        $adapter
            ->method('getInitialRunOnlyOptions')
            ->willReturn(['--configuration'])
        ;

        return $adapter;
    }
}
