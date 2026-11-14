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

use function array_filter;
use function array_values;
use function explode;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Console\ConsoleOutput;
use Infection\Process\Factory\MutantProcessContainerFactory;
use Infection\Process\MutantProcessContainer;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\TestFramework\Common\Throwable\InitialTestsFailed;
use Infection\TestFramework\Contracts\InitialRunResults;
use Infection\TestFramework\Coverage\CoverageChecker;
use Infection\TestFramework\Legacy\TestFrameworkExtraOptionsFilter;
use Infection\TestFramework\LegacyTestFrameworkBridge;
use Infection\TestFramework\ProvidesInitialRunOnlyOptions;
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
        $process = $this->createSuccessfulInitialRunProcess('output');
        $adapter = new FakeAwareAdapter(42.0);

        $initialTestsRunner = $this->createMock(InitialTestsRunner::class);
        $initialTestsRunner
            ->expects($this->once())
            ->method('run')
            ->with(['/bin/dummy'], false)
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
            initialTestsRunner: $initialTestsRunner,
        );

        $expected = new InitialRunResults(
            output: 'output',
            memoryUsage: 42.0,
        );

        $actual = $testFramework->executeInitialRun();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param list<string> $expectedTokens
     */
    #[DataProvider('provideLegacyArguments')]
    public function test_it_preserves_initial_arguments_for_space_splitting_adapters(string $rawArguments, array $expectedTokens): void
    {
        $adapter = $this->createMock(TestFrameworkAdapter::class);
        $adapter
            ->expects($this->once())
            ->method('getInitialTestRunCommandLine')
            ->with($rawArguments, ['-d', 'include_path=/path with  spaces'], true)
            ->willReturnCallback(self::createSpaceSplittingCommandLine(...))
        ;

        $initialTestsRunner = $this->createMock(InitialTestsRunner::class);
        $initialTestsRunner
            ->expects($this->once())
            ->method('run')
            ->with(['/bin/legacy', ...$expectedTokens], true)
            ->willReturn($this->createSuccessfulInitialRunProcess('output'))
        ;

        $testFramework = $this->createTestFramework(
            adapter: $adapter,
            initialTestsRunner: $initialTestsRunner,
            initialTestsPhpOptions: '-d include_path="/path with  spaces"',
            testFrameworkExtraOptions: $rawArguments,
            skipCoverage: true,
        );

        $testFramework->executeInitialRun();
    }

    /**
     * @param list<string> $expectedTokens
     */
    #[DataProvider('provideLegacyArguments')]
    public function test_it_preserves_mutant_arguments_for_space_splitting_adapters(string $rawArguments, array $expectedTokens): void
    {
        $mutant = MutantBuilder::withMinimalTestData()->build();
        $adapter = $this->createMock(TestFrameworkAdapter::class);
        $adapter
            ->expects($this->once())
            ->method('getMutantCommandLine')
            ->with(
                $mutant->getTests(),
                $mutant->getFilePath(),
                $mutant->getMutation()->getHash(),
                $mutant->getMutation()->getOriginalFilePath(),
                $rawArguments,
            )
            ->willReturnCallback(
                static fn (
                    array $_tests,
                    string $_mutantPath,
                    string $_hash,
                    string $_originalPath,
                    string $extraOptions,
                ): array => self::createSpaceSplittingCommandLine($extraOptions),
            )
        ;

        $processContainer = $this->createStub(MutantProcessContainer::class);
        $processFactory = $this->createMock(MutantProcessContainerFactory::class);
        $processFactory
            ->expects($this->once())
            ->method('create')
            ->with($adapter, $mutant, ['/bin/legacy', ...$expectedTokens])
            ->willReturn($processContainer)
        ;

        $testFramework = $this->createTestFramework(
            adapter: $adapter,
            processFactory: $processFactory,
            testFrameworkExtraOptions: $rawArguments,
        );

        $this->assertSame($processContainer, $testFramework->test($mutant));
    }

    public static function provideLegacyArguments(): iterable
    {
        yield 'empty arguments' => ['', []];

        yield 'apostrophe in a filename' => [
            "tests/Jenny's/FooTest.php",
            ["tests/Jenny's/FooTest.php"],
        ];

        yield 'existing quotes remain literal to space splitting' => [
            '--label="foo bar" tests/FooTest.php',
            ['--label="foo', 'bar"', 'tests/FooTest.php'],
        ];

        yield 'existing whitespace is retained' => [
            ' --verbose  tests/FooTest.php ',
            ['--verbose', 'tests/FooTest.php'],
        ];

        yield 'framework argument backslashes remain literal' => [
            'tests\Jenny\FooTest.php',
            ['tests\Jenny\FooTest.php'],
        ];
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
            adapter: $adapter,
            coverageChecker: $coverageChecker,
            initialTestsRunner: $initialTestsRunner,
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

    public function test_it_filters_initial_run_only_options_from_mutant_evaluation(): void
    {
        $mutant = MutantBuilder::withMinimalTestData()->build();
        $processContainer = $this->createStub(MutantProcessContainer::class);
        $adapter = $this->createInitialRunOnlyOptionsAdapter();
        $adapter
            ->expects($this->once())
            ->method('getMutantCommandLine')
            ->with(
                $mutant->getTests(),
                $mutant->getFilePath(),
                $mutant->getMutation()->getHash(),
                $mutant->getMutation()->getOriginalFilePath(),
                "--filter FooTest tests/Jenny's/FooTest.php --label=\"foo bar\"",
            )
            ->willReturn(['/bin/legacy', '--filter', 'FooTest'])
        ;

        $processFactoryMock = $this->createMock(MutantProcessContainerFactory::class);
        $processFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($adapter, $mutant, ['/bin/legacy', '--filter', 'FooTest'])
            ->willReturn($processContainer)
        ;

        $testFramework = $this->createTestFramework(
            adapter: $adapter,
            processFactory: $processFactoryMock,
            testFrameworkExtraOptions: "--configuration \"custom config.xml\" --filter FooTest tests/Jenny's/FooTest.php --label=\"foo bar\"",
        );

        $actual = $testFramework->test($mutant);

        $this->assertSame($processContainer, $actual);
    }

    /**
     * Mirrors the space splitting and empty-token filtering in the Codeception
     * and PhpSpec adapters, which do not interpret quotes or backslash escapes.
     *
     * @return list<string>
     */
    private static function createSpaceSplittingCommandLine(string $extraOptions): array
    {
        $tokens = array_values(array_filter(
            explode(' ', $extraOptions),
            static fn (string $token): bool => (bool) $token,
        ));

        return ['/bin/legacy', ...$tokens];
    }

    private function createTestFramework(
        ?TestFrameworkAdapter $adapter = null,
        ?ConsoleOutput $consoleOutput = null,
        ?CoverageChecker $coverageChecker = null,
        ?InitialTestsRunner $initialTestsRunner = null,
        ?MutantProcessContainerFactory $processFactory = null,
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
            testFrameworkExtraOptionsFilter: new TestFrameworkExtraOptionsFilter(),
        );
    }

    private function createSuccessfulInitialRunProcess(string $output): Process
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

    private function createInitialRunOnlyOptionsAdapter(): TestFrameworkAdapter&ProvidesInitialRunOnlyOptions&MockObject
    {
        /** @var TestFrameworkAdapter&ProvidesInitialRunOnlyOptions&MockObject $adapter */
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
