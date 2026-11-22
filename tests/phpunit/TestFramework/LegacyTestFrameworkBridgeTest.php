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
use Infection\Process\Factory\MutantProcessContainerFactory;
use Infection\Process\MutantProcessContainer;
use Infection\TestFramework\Common\InitialTestsRunner;
use Infection\TestFramework\Common\Throwable\InitialTestsFailed;
use Infection\TestFramework\Contracts\CompletedProcess;
use Infection\TestFramework\Contracts\InitialRunResults;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\TestFramework\Coverage\CoverageChecker;
use Infection\TestFramework\LegacyTestFrameworkBridge;
use Infection\TestFramework\Logger\LegacyTestFrameworkLogger;
use Infection\TestFramework\ProvidesInitialRunOnlyOptions;
use Infection\TestFramework\TestFrameworkExtraOptionsFilter;
use Infection\Tests\Configuration\ConfigurationBuilder;
use Infection\Tests\Fixtures\TestFramework\DummyTestFrameworkAdapter;
use Infection\Tests\Fixtures\TestFramework\FakeAwareAdapter;
use Infection\Tests\Mutant\MutantBuilder;
use Infection\Tests\TestFramework\Contracts\CompletedProcessBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\RuntimeException as SymfonyProcessRuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

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

    public function test_it_memoizes_the_adapter_version(): void
    {
        $adapter = $this->createMock(TestFrameworkAdapter::class);
        $adapter
            ->expects($this->once())
            ->method('getVersion')
            ->willReturn('1.0.0')
        ;
        $testFramework = $this->createTestFramework(adapter: $adapter);

        $this->assertSame('1.0.0', $testFramework->getVersion());
        $this->assertSame('1.0.0', $testFramework->getVersion());
    }

    #[DataProvider('versionDetectionFailureProvider')]
    public function test_it_memoizes_an_unknown_version_when_legacy_version_detection_fails(Throwable $exception): void
    {
        $adapter = $this->createMock(TestFrameworkAdapter::class);
        $adapter
            ->expects($this->once())
            ->method('getVersion')
            ->willThrowException($exception)
        ;
        $adapter
            ->method('getName')
            ->willReturn('LegacyFramework')
        ;
        $logger = $this->createMock(LegacyTestFrameworkLogger::class);
        $logger
            ->expects($this->once())
            ->method('logVersionDetectionFailure')
            ->with('LegacyFramework', $exception)
        ;
        $testFramework = $this->createTestFramework(
            adapter: $adapter,
            logger: $logger,
        );

        $this->assertSame(TestFramework::UNKNOWN_VERSION, $testFramework->getVersion());
        $this->assertSame(TestFramework::UNKNOWN_VERSION, $testFramework->getVersion());
    }

    public static function versionDetectionFailureProvider(): iterable
    {
        yield 'legacy parsing failure' => [new InvalidArgumentException('Unrecognized version output.')];

        yield 'version command failure' => [new SymfonyProcessRuntimeException('Could not launch the version command.')];
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
        $process = $this->createSuccessfulInitialRunProcess('output');
        $commandLine = (new Process($process->command))->getCommandLine();
        $adapter = new FakeAwareAdapter(42.0);

        $initialTestsRunner = $this->createMock(InitialTestsRunner::class);

        $coverageChecker = $this->createMock(CoverageChecker::class);
        $coverageChecker
            ->expects($this->once())
            ->method('checkCoverageHasBeenGenerated')
            ->with($commandLine, 'output')
        ;

        $testFramework = $this->createTestFramework(
            adapter: $adapter,
            coverageChecker: $coverageChecker,
            initialTestsRunner: $initialTestsRunner,
        );

        $initialTestsRunner
            ->expects($this->once())
            ->method('run')
            ->with($testFramework, ['/bin/dummy'], false)
            ->willReturn($process)
        ;

        $expected = new InitialRunResults(
            output: 'output',
            memoryUsage: 42.0,
        );

        $actual = $testFramework->executeInitialRun();

        $this->assertEquals($expected, $actual);
    }

    public function test_it_forwards_initial_run_options(): void
    {
        $process = $this->createSuccessfulInitialRunProcess('output');
        $adapter = $this->createMock(TestFrameworkAdapter::class);
        $adapter
            ->expects($this->once())
            ->method('getInitialTestRunCommandLine')
            ->with('--verbose', ['-d', 'memory_limit=1G'], true)
            ->willReturn(['/bin/dummy', '--verbose'])
        ;

        $initialTestsRunner = $this->createMock(InitialTestsRunner::class);

        $testFramework = $this->createTestFramework(
            adapter: $adapter,
            initialTestsRunner: $initialTestsRunner,
            initialTestsPhpOptions: '-d memory_limit=1G',
            testFrameworkExtraOptions: '--verbose',
            skipCoverage: true,
        );

        $initialTestsRunner
            ->expects($this->once())
            ->method('run')
            ->with($testFramework, ['/bin/dummy', '--verbose'], true)
            ->willReturn($process)
        ;

        $testFramework->executeInitialRun();
    }

    public function test_it_throws_when_the_initial_run_fails(): void
    {
        $process = $this->createFailedInitialRunProcess();
        $commandLine = (new Process($process->command))->getCommandLine();
        $adapter = $this->createMock(TestFrameworkAdapter::class);
        $adapter
            ->expects($this->once())
            ->method('getInitialTestsFailRecommendations')
            ->with($commandLine)
            ->willReturn('Check the legacy framework configuration.')
        ;

        $failure = InitialTestsFailed::fromCompletedProcess($process, 'LegacyFramework', 'tests', 'Check the legacy framework configuration.');
        $initialTestsRunner = $this->createMock(InitialTestsRunner::class);
        $initialTestsRunner
            ->expects($this->once())
            ->method('run')
            ->with(
                $this->isInstanceOf(TestFramework::class),
                [],
                false,
                'tests',
                $this->callback(function (Closure $recommendations) use ($commandLine): bool {
                    $this->assertSame(
                        'Check the legacy framework configuration.',
                        $recommendations($commandLine),
                    );

                    return true;
                }),
            )
            ->willThrowException($failure)
        ;

        $coverageChecker = $this->createMock(CoverageChecker::class);
        $coverageChecker
            ->expects($this->never())
            ->method('checkCoverageHasBeenGenerated')
        ;

        $testFramework = $this->createTestFramework(
            adapter: $adapter,
            coverageChecker: $coverageChecker,
            initialTestsRunner: $initialTestsRunner,
        );

        $this->expectExceptionObject($failure);

        $testFramework->executeInitialRun();
    }

    public function test_it_reports_unknown_memory_usage_when_the_legacy_adapter_does_not_report_it(): void
    {
        $initialTestsRunner = $this->createMock(InitialTestsRunner::class);
        $initialTestsRunner
            ->method('run')
            ->willReturn($this->createSuccessfulInitialRunProcess('output'))
        ;

        $testFramework = $this->createTestFramework(
            adapter: new DummyTestFrameworkAdapter(),
            initialTestsRunner: $initialTestsRunner,
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
        $initialTestsRunner = $this->createMock(InitialTestsRunner::class);
        $initialTestsRunner
            ->method('run')
            ->willReturn($this->createSuccessfulInitialRunProcess('output'))
        ;

        $testFramework = $this->createTestFramework(
            adapter: new FakeAwareAdapter(-1.0),
            initialTestsRunner: $initialTestsRunner,
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

    private function createTestFramework(
        ?TestFrameworkAdapter $adapter = null,
        ?ConsoleOutput $consoleOutput = null,
        ?CoverageChecker $coverageChecker = null,
        ?InitialTestsRunner $initialTestsRunner = null,
        ?MutantProcessContainerFactory $processFactory = null,
        ?TestFrameworkExtraOptionsFilter $testFrameworkExtraOptionsFilter = null,
        bool $skipInitialTests = false,
        ?string $initialTestsPhpOptions = null,
        string $testFrameworkExtraOptions = '',
        bool $skipCoverage = false,
        ?LegacyTestFrameworkLogger $logger = null,
    ): TestFramework {
        return new LegacyTestFrameworkBridge(
            adapter: $adapter ?? new FakeAwareAdapter(1.0),
            consoleOutput: $consoleOutput ?? $this->createStub(ConsoleOutput::class),
            coverageChecker: $coverageChecker ?? $this->createStub(CoverageChecker::class),
            initialTestsRunner: $initialTestsRunner ?? $this->createStub(InitialTestsRunner::class),
            config: ConfigurationBuilder::withMinimalTestData()
                ->withInitialTestsPhpOptions($initialTestsPhpOptions)
                ->withTestFrameworkExtraOptions($testFrameworkExtraOptions)
                ->withSkipCoverage($skipCoverage)
                ->withSkipInitialTests($skipInitialTests)
                ->build(),
            processFactory: $processFactory ?? $this->createStub(MutantProcessContainerFactory::class),
            testFrameworkExtraOptionsFilter: $testFrameworkExtraOptionsFilter ?? $this->createStub(TestFrameworkExtraOptionsFilter::class),
            logger: $logger ?? $this->createStub(LegacyTestFrameworkLogger::class),
        );
    }

    private function createSuccessfulInitialRunProcess(string $output): CompletedProcess
    {
        return CompletedProcessBuilder::withMinimalTestData()
            ->withCommand(['/tmp/phpunit'])
            ->withStdout($output)
            ->build()
        ;
    }

    private function createFailedInitialRunProcess(): CompletedProcess
    {
        return CompletedProcessBuilder::withMinimalTestData()
            ->withCommand(['/tmp/phpunit'])
            ->withExitCode(1)
            ->withStdout('output')
            ->withStderr('error')
            ->build()
        ;
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
