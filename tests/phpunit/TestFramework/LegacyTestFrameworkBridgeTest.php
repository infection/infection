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

use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Console\ConsoleOutput;
use Infection\Process\Factory\MutantProcessContainerFactory;
use Infection\Process\MutantProcessContainer;
use Infection\Process\Runner\InitialTestsFailed;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\TestFramework\Contracts\InitialRunResults;
use Infection\TestFramework\Coverage\CoverageChecker;
use Infection\TestFramework\LegacyTestFrameworkBridge;
use Infection\TestFramework\ProvidesInitialRunOnlyOptions;
use Infection\TestFramework\TestFrameworkExtraOptionsFilter;
use Infection\Tests\Configuration\ConfigurationBuilder;
use Infection\Tests\Fixtures\TestFramework\DummyTestFrameworkAdapter;
use Infection\Tests\Fixtures\TestFramework\FakeAwareAdapter;
use Infection\Tests\Mutant\MutantBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

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

    public function test_it_checks_existing_coverage_when_initial_tests_are_skipped(): void
    {
        $consoleOutput = $this->createMock(ConsoleOutput::class);
        $consoleOutput
            ->expects($this->once())
            ->method('logSkippingInitialTests');

        $coverageChecker = $this->createMock(CoverageChecker::class);
        $coverageChecker
            ->expects($this->once())
            ->method('checkCoverageExists');

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
            ->method('logSkippingInitialTests');

        $coverageChecker = $this->createMock(CoverageChecker::class);
        $coverageChecker
            ->expects($this->never())
            ->method('checkCoverageExists');

        $testFramework = $this->createTestFramework(
            consoleOutput: $consoleOutput,
            coverageChecker: $coverageChecker,
        );

        $testFramework->checkRequirements();
    }

    public function test_it_executes_initial_run_and_reports_memory_usage(): void
    {
        $process = $this->createSuccessfulInitialRunProcess('output');

        $initialTestsRunner = $this->createMock(InitialTestsRunner::class);
        $initialTestsRunner
            ->expects($this->once())
            ->method('run')
            ->with('', [''], false)
            ->willReturn($process)
        ;

        $coverageChecker = $this->createMock(CoverageChecker::class);
        $coverageChecker
            ->expects($this->once())
            ->method('checkCoverageHasBeenGenerated')
            ->with('/tmp/phpunit', 'output')
        ;

        $testFramework = $this->createTestFramework(
            adapter: new FakeAwareAdapter(42.0),
            coverageChecker: $coverageChecker,
            initialTestsRunner: $initialTestsRunner,
        );

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

        $initialTestsRunner = $this->createMock(InitialTestsRunner::class);
        $initialTestsRunner
            ->expects($this->once())
            ->method('run')
            ->with('--verbose', ['-d', 'memory_limit=1G'], true)
            ->willReturn($process)
        ;

        $testFramework = $this->createTestFramework(
            initialTestsRunner: $initialTestsRunner,
            initialTestsPhpOptions: '-d memory_limit=1G',
            testFrameworkExtraOptions: '--verbose',
            skipCoverage: true,
        );

        $testFramework->executeInitialRun();
    }

    public function test_it_throws_when_the_initial_run_fails(): void
    {
        $initialTestsRunner = $this->createMock(InitialTestsRunner::class);
        $initialTestsRunner
            ->expects($this->once())
            ->method('run')
            ->willReturn($this->createFailedInitialRunProcess())
        ;

        $coverageChecker = $this->createMock(CoverageChecker::class);
        $coverageChecker
            ->expects($this->never())
            ->method('checkCoverageHasBeenGenerated')
        ;

        $testFramework = $this->createTestFramework(
            adapter: new DummyTestFrameworkAdapter(),
            coverageChecker: $coverageChecker,
            initialTestsRunner: $initialTestsRunner,
        );

        $this->expectException(InitialTestsFailed::class);

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

        $processFactoryMock = $this->createMock(MutantProcessContainerFactory::class);
        $processFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($mutant, '')
            ->willReturn($processContainer);

        $testFramework = $this->createTestFramework(processFactory: $processFactoryMock);

        $actual = $testFramework->test($mutant);

        $this->assertSame($processContainer, $actual);
    }

    public function test_it_filters_initial_run_only_options_from_mutant_evaluation(): void
    {
        $mutant = MutantBuilder::withMinimalTestData()->build();
        $processContainer = $this->createStub(MutantProcessContainer::class);

        $processFactoryMock = $this->createMock(MutantProcessContainerFactory::class);
        $processFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($mutant, '--filter FooTest')
            ->willReturn($processContainer);

        $testFrameworkExtraOptionsFilter = $this->createMock(TestFrameworkExtraOptionsFilter::class);
        $testFrameworkExtraOptionsFilter
            ->expects($this->once())
            ->method('filterForMutantProcess')
            ->with('--configuration phpunit.xml --filter FooTest', ['--configuration'])
            ->willReturn('--filter FooTest')
        ;

        $testFramework = $this->createTestFramework(
            adapter: $this->createInitialRunOnlyOptionsAdapter(),
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
    ): LegacyTestFrameworkBridge {
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
        );
    }

    private function createSuccessfulInitialRunProcess(string $output): Process
    {
        $process = $this->createMock(Process::class);
        $process
            ->expects($this->once())
            ->method('isSuccessful')
            ->willReturn(true);
        $process
            ->method('getOutput')
            ->willReturn($output);
        $process
            ->method('getCommandLine')
            ->willReturn('/tmp/phpunit');

        return $process;
    }

    private function createFailedInitialRunProcess(): Process
    {
        $process = $this->createMock(Process::class);
        $process
            ->expects($this->once())
            ->method('isSuccessful')
            ->willReturn(false);
        $process
            ->method('getExitCode')
            ->willReturn(1);
        $process
            ->method('getCommandLine')
            ->willReturn('/tmp/phpunit');
        $process
            ->method('getOutput')
            ->willReturn('output');
        $process
            ->method('getErrorOutput')
            ->willReturn('error');

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
            ->willReturn(['--configuration']);

        return $adapter;
    }
}
