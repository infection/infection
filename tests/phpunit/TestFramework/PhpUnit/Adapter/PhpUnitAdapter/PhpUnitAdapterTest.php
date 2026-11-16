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

namespace Infection\Tests\TestFramework\PhpUnit\Adapter\PhpUnitAdapter;

use function array_map;
use function array_slice;
use Closure;
use function implode;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\AbstractTestFramework\InvalidVersion;
use Infection\Config\ValueProvider\PCOVDirectoryProvider;
use Infection\Configuration\Configuration;
use Infection\FileSystem\FileSystem;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\Mutant;
use Infection\Mutant\TestFrameworkMutantExecutionResultFactory;
use Infection\Process\Factory\MutantProcessContainerFactory;
use Infection\Process\MutantProcess;
use Infection\Process\MutantProcessContainer;
use Infection\TestFramework\Common\CommandLineBuilder;
use Infection\TestFramework\Common\InitialTestsRunner;
use Infection\TestFramework\Common\Throwable\InitialTestsFailed;
use Infection\TestFramework\Common\VersionParser;
use Infection\TestFramework\Contracts\InitialRunResults;
use Infection\TestFramework\Contracts\ShellCommandRunner;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\TestFramework\Contracts\Throwable\InitialTestsFailed as InitialTestsFailedContract;
use Infection\TestFramework\Contracts\Throwable\RequirementChecksFailed;
use Infection\TestFramework\Coverage\CoverageCheckerFactory;
use Infection\TestFramework\Coverage\JUnit\JUnitReportLocator;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageLocator;
use Infection\TestFramework\MapSourceClassToTestStrategy;
use Infection\TestFramework\PhpUnit\Adapter\PhpUnitAdapter;
use Infection\TestFramework\PhpUnit\CommandLine\ArgumentsAndOptionsBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\InitialConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\MutationConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\InvalidPhpUnitConfiguration;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationManipulator;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationVersionProvider;
use Infection\TestFramework\PhpUnit\Log\PhpUnitLogger;
use Infection\TestFramework\PhpUnit\MemoryLimiter;
use Infection\TestFramework\PhpUnit\MemoryLimiterEnvironment;
use Infection\TestFramework\TestFrameworkExtraOptionsFilter;
use Infection\TestFramework\Tracing\TestRunOrderResolver;
use Infection\Tests\Configuration\ConfigurationBuilder;
use Infection\Tests\Mutant\MutantBuilder;
use Infection\Tests\Mutation\MutationBuilder;
use Infection\Tests\TestFramework\Contracts\CompletedProcessBuilder;
use Infection\Tests\TestingUtility\PHPUnit\ExpectsThrowables;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use Symfony\Component\Process\Exception\RuntimeException as SymfonyProcessRuntimeException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(PhpUnitAdapter::class)]
final class PhpUnitAdapterTest extends TestCase
{
    use ExpectsThrowables;

    private const string DEFAULT_PHPUNIT_VERSION = '9.0';

    private const string PHP_EXECUTABLE = '/path/to/php';

    private PhpUnitAdapter $adapter;

    private MockObject&PCOVDirectoryProvider $pcovDirectoryProvider;

    private MockObject&FileSystem $fileSystemMock;

    private MockObject&PhpExecutableFinder $phpExecutableFinderMock;

    protected function setUp(): void
    {
        $this->pcovDirectoryProvider = $this->createMock(PCOVDirectoryProvider::class);
        $this->fileSystemMock = $this->createMock(FileSystem::class);
        $this->phpExecutableFinderMock = $this->createMock(PhpExecutableFinder::class);
        $this->phpExecutableFinderMock
            ->method('find')
            ->willReturn(self::PHP_EXECUTABLE)
        ;

        $this->adapter = $this->createAdapter(
            <<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <phpunit/>
                XML,
        );
    }

    public function test_it_has_a_name(): void
    {
        $this->fileSystemMock
            ->expects($this->never())
            ->method('dumpFile')
        ;
        $this->pcovDirectoryProvider
            ->expects($this->never())
            ->method('shouldProvide')
        ;

        $this->assertSame('PHPUnit', $this->adapter->getName());
    }

    public function test_it_supports_junit_reports(): void
    {
        $this->fileSystemMock
            ->expects($this->never())
            ->method('dumpFile')
        ;
        $this->pcovDirectoryProvider
            ->expects($this->never())
            ->method('shouldProvide')
        ;

        $this->assertTrue($this->adapter->hasJUnitReport());
    }

    #[DataProvider('initialRunOutputProvider')]
    public function test_it_executes_the_initial_run(
        string $output,
        ?float $memoryUsage,
    ): void {
        $process = CompletedProcessBuilder::withMinimalTestData()
            ->withStdout($output)
            ->build()
        ;

        $initialTestsRunner = $this->createMock(InitialTestsRunner::class);
        $configuration = ConfigurationBuilder::withMinimalTestData()
            ->withInitialTestsPhpOptions('-d memory_limit=1G')
            ->withTestFrameworkExtraOptions('--verbose --warn-when-php-is-not-configured-for-development')
            ->withSkipCoverage(true)
            ->build()
        ;
        $adapter = $this->createAdapter(
            '<phpunit/>',
            configuration: $configuration,
            initialTestsRunner: $initialTestsRunner,
        );

        $expectedCommand = [
            self::PHP_EXECUTABLE,
            '-d',
            'memory_limit=1G',
            '/path/to/phpunit',
            '--configuration',
            '/tmp/phpunitConfiguration.initial.infection.xml',
            '--verbose',
            '--warn-when-php-is-not-configured-for-development',
        ];
        $initialTestsRunner
            ->expects($this->once())
            ->method('run')
            ->with(
                $adapter,
                $expectedCommand,
                true,
            )
            ->willReturn($process)
        ;
        $this->fileSystemMock
            ->expects($this->exactly(2))
            ->method('isReadableFile')
            ->willReturn(true)
        ;

        $this->assertEquals(
            new InitialRunResults(
                $output,
                $memoryUsage,
            ),
            $adapter->executeInitialRun(),
            'The initial run must preserve the output and normalize unknown memory usage.',
        );
    }

    public static function initialRunOutputProvider(): iterable
    {
        yield 'single-digit memory usage' => ['Memory: 8.00MB', 8.0];

        yield 'multiple-digit memory usage' => ['Memory: 68.00MB', 68.0];

        yield 'space before the unit' => ['Memory: 68.00 MB', 68.0];

        yield 'unknown memory usage' => ['Time: 2.51 seconds', null];
    }

    public function test_it_reports_missing_generated_coverage_as_an_initial_run_failure(): void
    {
        $process = CompletedProcessBuilder::withMinimalTestData()->build();
        $initialTestsRunner = $this->createStub(InitialTestsRunner::class);
        $initialTestsRunner
            ->method('run')
            ->willReturn($process)
        ;
        $adapter = $this->createAdapter(
            '<phpunit/>',
            initialTestsRunner: $initialTestsRunner,
        );

        $this->expectException(InitialTestsFailedContract::class);
        $this->expectExceptionMessage('The code coverage generated by the initial test run is invalid.');

        $adapter->executeInitialRun();
    }

    #[DataProvider('initialRunFailureProvider')]
    public function test_it_throws_when_the_initial_run_fails(string $version, string $recommendations): void
    {
        $process = CompletedProcessBuilder::withMinimalTestData()
            ->withCommand(['vendor/bin/phpunit', '--configuration', 'failing.xml'])
            ->withExitCode(1)
            ->build()
        ;
        $commandLine = (new Process($process->command))->getCommandLine();

        $initialTestsRunner = $this->createMock(InitialTestsRunner::class);
        $adapter = $this->createAdapter(
            '<phpunit/>',
            version: $version,
            initialTestsRunner: $initialTestsRunner,
        );
        $failure = InitialTestsFailed::fromCompletedProcess($process, 'PHPUnit', 'tests', $recommendations);
        $initialTestsRunner
            ->expects($this->once())
            ->method('run')
            ->with(
                $adapter,
                [
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ],
                false,
                'tests',
                $this->callback(
                    /**
                     * @param Closure(string): string $getRecommendations
                     */
                    function (Closure $getRecommendations) use ($commandLine, $recommendations): bool {
                        $this->assertThat(
                            $getRecommendations($commandLine),
                            $this->stringContains($recommendations),
                        );

                        return true;
                    },
                ),
            )
            ->willThrowException($failure)
        ;
        $this->fileSystemMock
            ->expects($this->never())
            ->method('isReadableFile')
        ;

        $this->expectExceptionObject($failure);

        $adapter->executeInitialRun();
    }

    public static function initialRunFailureProvider(): iterable
    {
        yield 'no random execution before PHPUnit 7.2' => [
            '7.1',
            <<<'TEXT'
                Check the executed command to identify the problem:
                TEXT,
        ];

        yield 'random execution from PHPUnit 7.2' => [
            '7.2',
            <<<'TEXT'
                Infection runs the test suite in a RANDOM order. Make sure your tests do not have hidden dependencies.

                You can add these attributes to `phpunit.xml` to check it: <phpunit executionOrder="random" resolveDependencies="true" ...
                TEXT,
        ];

        yield 'defects and random execution on PHPUnit 12.2.7' => [
            '12.2.7',
            <<<'TEXT'
                Infection runs the test suite in a RANDOM order. Make sure your tests do not have hidden dependencies.

                You can add these attributes to `phpunit.xml` to check it: <phpunit executionOrder="defects,random" resolveDependencies="true" ...
                TEXT,
        ];
    }

    public function test_it_filters_initial_run_only_options_from_mutant_evaluation(): void
    {
        $mutant = MutantBuilder::withMinimalTestData()->build();
        $processContainer = $this->createStub(MutantProcessContainer::class);
        $processFactory = $this->createMock(MutantProcessContainerFactory::class);
        $adapter = $this->createAdapter(
            '<phpunit/>',
            configuration: ConfigurationBuilder::withMinimalTestData()
                ->withTestFrameworkExtraOptions('--configuration phpunit.xml --filter Foo --testsuite unit --warn-when-php-is-not-configured-for-development --verbose')
                ->build(),
            processFactory: $processFactory,
        );
        $processFactory
            ->expects($this->once())
            ->method('create')
            ->with(
                [
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.' . $mutant->getMutation()->getHash() . '.infection.xml',
                    '--verbose',
                ],
                $mutant,
                $this->isInstanceOf(Closure::class),
                $this->isInstanceOf(Closure::class),
            )
            ->willReturn($processContainer)
        ;

        $this->assertSame(
            $processContainer,
            $adapter->test($mutant),
            'Mutant evaluation must use the process factory with initial-run options removed.',
        );
    }

    #[DataProvider('mutantOutputProvider')]
    public function test_it_classifies_mutant_output(string $output, int $exitCode, DetectionStatus $expected): void
    {
        $actual = $this->classifyMutantOutput($output, $exitCode);

        $this->assertSame($expected, $actual);
    }

    public static function mutantOutputProvider(): iterable
    {
        yield 'passing tests let the mutant escape' => ['OK (1 test, 1 assertion)', 0, DetectionStatus::ESCAPED];

        yield 'no matching tests let the mutant escape' => ['No tests executed!', 0, DetectionStatus::ESCAPED];

        yield 'test failures kill the mutant' => ['FAILURES!', 1, DetectionStatus::KILLED_BY_TESTS];

        yield 'syntax errors are reported separately' => ['ParseError: syntax error', 1, DetectionStatus::SYNTAX_ERROR];
    }

    #[DataProvider('skipInitialTestsProvider')]
    public function test_it_checks_requirements(bool $skipInitialTests): void
    {
        $logger = $this->createMock(PhpUnitLogger::class);
        $logger
            ->expects($this->exactly($skipInitialTests ? 1 : 0))
            ->method('logSkippingInitialTests')
        ;
        $this->fileSystemMock
            ->expects($this->exactly($skipInitialTests ? 2 : 0))
            ->method('isReadableFile')
            ->willReturn(true)
        ;
        $adapter = $this->createAdapter(
            '<phpunit/>',
            configuration: ConfigurationBuilder::withMinimalTestData()
                ->withSkipCoverage(true)
                ->withSkipInitialTests($skipInitialTests)
                ->build(),
            logger: $logger,
        );

        $adapter->checkRequirements();
    }

    public static function skipInitialTestsProvider(): iterable
    {
        yield 'initial tests are run' => [false];

        yield 'initial tests are skipped' => [true];
    }

    #[DataProvider('missingCoverageReportProvider')]
    public function test_it_reports_missing_coverage_as_a_requirement_failure(bool $indexExists, string $expectedMessage): void
    {
        $this->fileSystemMock
            ->expects($this->atLeastOnce())
            ->method('isReadableFile')
            ->willReturnMap([
                ['/coverage-xml/index.xml', $indexExists],
                ['/junit.xml', false],
            ])
        ;
        $adapter = $this->createAdapter(
            '<phpunit/>',
            configuration: ConfigurationBuilder::withMinimalTestData()
                ->withSkipCoverage(true)
                ->withSkipInitialTests(true)
                ->build(),
        );

        $this->expectException(RequirementChecksFailed::class);
        $this->expectExceptionMessage($expectedMessage);

        $adapter->checkRequirements();
    }

    public static function missingCoverageReportProvider(): iterable
    {
        yield 'missing XML index' => [false, 'Could not find the "index.xml" file.'];

        yield 'missing JUnit report' => [true, 'Could not find the JUnit file report.'];
    }

    public function test_it_memoizes_an_unknown_version_when_the_version_command_fails(): void
    {
        $exception = new SymfonyProcessRuntimeException('Could not launch the version command.');
        $shellCommandRunner = $this->createMock(ShellCommandRunner::class);
        $shellCommandRunner
            ->expects($this->once())
            ->method('mustRun')
            ->willThrowException($exception)
        ;
        $logger = $this->createMock(PhpUnitLogger::class);
        $logger
            ->expects($this->once())
            ->method('logVersionDetectionFailure')
            ->with($exception)
        ;

        $adapter = $this->createAdapter(
            '<phpunit/>',
            version: null,
            shellCommandRunner: $shellCommandRunner,
            logger: $logger,
        );

        $this->assertSame(TestFramework::UNKNOWN_VERSION, $adapter->getVersion());
        $this->assertSame(TestFramework::UNKNOWN_VERSION, $adapter->getVersion());
    }

    public function test_it_rejects_unrecognizable_version_output(): void
    {
        $shellCommandRunner = $this->createStub(ShellCommandRunner::class);
        $shellCommandRunner
            ->method('mustRun')
            ->willReturn('unrecognizable output')
        ;
        $logger = $this->createMock(PhpUnitLogger::class);
        $logger
            ->expects($this->never())
            ->method('logVersionDetectionFailure')
        ;

        $adapter = $this->createAdapter(
            '<phpunit/>',
            version: null,
            shellCommandRunner: $shellCommandRunner,
            logger: $logger,
        );

        $this->expectException(InvalidVersion::class);
        $this->expectExceptionMessage('Could not recognise the test framework version for PHPUnit for the value "unrecognizable output".');

        $adapter->getVersion();
    }

    public function test_it_retrieves_version(): void
    {
        $shellCommandRunner = $this->createMock(ShellCommandRunner::class);
        $shellCommandRunner
            ->expects($this->once())
            ->method('mustRun')
            ->with([self::PHP_EXECUTABLE, '/path/to/phpunit', '--version'])
            ->willReturn('PHPUnit 10.5.1 by Sebastian Bergmann and contributors.')
        ;

        $expected = '10.5.1';

        $adapter = $this->createAdapter(
            <<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <phpunit/>
                XML,
            version: null,
            shellCommandRunner: $shellCommandRunner,
        );

        $actual = $adapter->getVersion();

        $this->assertSame($expected, $actual);
        $this->assertSame($expected, $adapter->getVersion());
    }

    #[DataProvider('passOutputProvider')]
    public function test_it_can_tell_if_tests_pass_from_the_output(string $output, bool $expected): void
    {
        $status = $this->classifyMutantOutput($output, 0);

        $this->assertSame($expected, $status === DetectionStatus::ESCAPED);
    }

    #[DataProvider('syntaxErrorOutputProvider')]
    public function test_it_can_tell_if_there_is_a_syntax_error_from_the_output(string $output, bool $expected): void
    {
        $status = $this->classifyMutantOutput($output, 1);

        $this->assertSame($expected, $status === DetectionStatus::SYNTAX_ERROR);
    }

    #[DataProvider('configurationValidationProvider')]
    public function test_it_validates_configuration_only_when_supported(
        string $version,
        bool $supportsValidation,
    ): void {
        $shellCommandRunner = $this->createMock(ShellCommandRunner::class);
        $shellCommandRunner
            ->expects($supportsValidation ? $this->once() : $this->never())
            ->method('mustRun')
            ->with([
                self::PHP_EXECUTABLE,
                '-d',
                'memory_limit=512M',
                '/path/to/phpunit',
                '--configuration',
                '/path/to/project/tools/phpunit/phpunit.xml',
                '--validate-configuration',
            ])
            ->willReturn('Configuration is valid.')
        ;

        $this->fileSystemMock
            ->expects($this->once())
            ->method('dumpFile')
        ;

        $initialTestsRunner = $this->createMock(InitialTestsRunner::class);
        $initialTestsRunner
            ->expects($this->once())
            ->method('run')
            ->with(
                $this->isInstanceOf(TestFramework::class),
                [
                    self::PHP_EXECUTABLE,
                    '-d',
                    'memory_limit=512M',
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                ],
                true,
            )
            ->willReturn(CompletedProcessBuilder::withMinimalTestData()->build())
        ;
        $adapter = $this->createAdapter(
            '<phpunit/>',
            version: $version,
            shellCommandRunner: $shellCommandRunner,
            configuration: ConfigurationBuilder::withMinimalTestData()
                ->withInitialTestsPhpOptions('-d memory_limit=512M')
                ->withSkipCoverage(true)
                ->build(),
            initialTestsRunner: $initialTestsRunner,
        );

        $this->fileSystemMock->method('isReadableFile')->willReturn(true);
        $adapter->executeInitialRun();
    }

    public function test_it_stops_before_building_the_initial_configuration_when_validation_fails(): void
    {
        $failure = new SymfonyProcessRuntimeException(
            'The configuration does not validate against the PHPUnit schema.',
            42,
        );

        $shellCommandRunner = $this->createMock(ShellCommandRunner::class);
        $shellCommandRunner
            ->expects($this->once())
            ->method('mustRun')
            ->with([
                self::PHP_EXECUTABLE,
                '/path/to/phpunit',
                '--configuration',
                '/path/to/project/tools/phpunit/phpunit.xml',
                '--validate-configuration',
            ])
            ->willThrowException($failure)
        ;

        $this->fileSystemMock
            ->expects($this->never())
            ->method('dumpFile')
        ;

        $adapter = $this->createAdapter(
            '<phpunit/>',
            version: '13.2.0',
            shellCommandRunner: $shellCommandRunner,
        );

        $exception = $this->expectToThrow(
            static fn () => $adapter->executeInitialRun(),
        );

        $this->assertInstanceOf(InvalidPhpUnitConfiguration::class, $exception);
        $this->assertSame(
            'Could not validate the PHPUnit configuration file "/path/to/project/tools/phpunit/phpunit.xml".',
            $exception->getMessage(),
        );
        $this->assertSame(42, $exception->getCode());
        $this->assertSame($failure, $exception->getPrevious());
    }

    public static function configurationValidationProvider(): iterable
    {
        yield 'older major' => ['12.5.37', false];

        yield 'previous minor' => ['13.1.9', false];

        yield 'first supported version' => ['13.2.0', true];

        yield 'later version' => ['13.3.0', true];
    }

    #[DataProvider('initialTestRunProvider')]
    public function test_it_provides_initial_test_run_command_line(
        InitialTestRunScenario $scenario,
    ): void {
        $this->fileSystemMock
            ->expects($this->once())
            // Checking the content of the dumped XML is out of the scope of this test.
            // It is doable, but would be unnecessarily verbose.
            ->method('dumpFile')
        ;

        $this->pcovDirectoryProvider
            ->expects($scenario->skipCoverage ? $this->never() : $this->once())
            ->method('shouldProvide')
            ->willReturn($scenario->pcovDirectory !== '')
        ;
        $this->pcovDirectoryProvider
            ->expects($this->atMost(1))
            ->method('getDirectory')
            ->willReturn($scenario->pcovDirectory)
        ;

        $process = CompletedProcessBuilder::withMinimalTestData()->build();
        $initialTestsRunner = $this->createMock(InitialTestsRunner::class);
        $initialTestsRunner
            ->expects($this->once())
            ->method('run')
            ->with($this->isInstanceOf(TestFramework::class), $scenario->expected, $scenario->skipCoverage)
            ->willReturn($process)
        ;
        $this->fileSystemMock->method('isReadableFile')->willReturn(true);

        $adapter = $this->createAdapter(
            testFrameworkConfigContent: $scenario->testFrameworkConfigContent,
            version: $scenario->version,
            filteredSourceFilesToMutate: $scenario->filteredSourceFilesToMutate,
            executeOnlyCoveringTestCases: $scenario->executeOnlyCoveringTestCases,
            mapSourceClassToTestStrategy: $scenario->mapSourceClassToTestStrategy,
            configuration: ConfigurationBuilder::withMinimalTestData()
                ->withTestFrameworkExtraOptions($scenario->extraOptions)
                ->withInitialTestsPhpOptions(implode(' ', $scenario->phpExtraArgs))
                ->withSkipCoverage($scenario->skipCoverage)
                ->build(),
            initialTestsRunner: $initialTestsRunner,
        );

        $adapter->executeInitialRun();
    }

    #[DataProvider('mutantCommandLineProvider')]
    public function test_it_provides_mutant_command_line(
        MutantCommandLineScenario $scenario,
    ): void {
        $this->fileSystemMock
            ->expects($this->exactly(2))
            // Checking the content of the dumped XML and generated autoload file is out of the scope of this test.
            ->method('dumpFile')
        ;

        $this->pcovDirectoryProvider
            ->expects($this->never())
            ->method('shouldProvide')
        ;

        $mutant = MutantBuilder::withMinimalTestData()
            ->withMutantFilePath($scenario->mutatedFilePath)
            ->withMutation(
                MutationBuilder::withMinimalTestData()
                    ->withHash($scenario->mutationHash)
                    ->withOriginalFilePath($scenario->mutationOriginalFilePath)
                    ->withTests($scenario->coverageTests)
                    ->build(),
            )
            ->build()
        ;
        $processContainer = $this->createStub(MutantProcessContainer::class);
        $processFactory = $this->createMock(MutantProcessContainerFactory::class);
        $processFactory
            ->expects($this->once())
            ->method('create')
            ->with(
                $scenario->expected,
                $mutant,
                $this->isInstanceOf(Closure::class),
                $this->isInstanceOf(Closure::class),
            )
            ->willReturn($processContainer)
        ;

        $adapter = $this->createAdapter(
            testFrameworkConfigContent: $scenario->testFrameworkConfigContent,
            version: $scenario->version,
            executeOnlyCoveringTestCases: $scenario->executeOnlyCoveringTestCases,
            configuration: ConfigurationBuilder::withMinimalTestData()
                ->withTestFrameworkExtraOptions($scenario->extraOptions)
                ->build(),
            processFactory: $processFactory,
        );

        $actual = $adapter->test($mutant);

        $this->assertSame($processContainer, $actual);
    }

    public function test_it_applies_the_initial_run_memory_usage_to_mutant_commands(): void
    {
        $process = CompletedProcessBuilder::withMinimalTestData()
            ->withStdout('Memory: 12.50MB')
            ->build()
        ;
        $initialTestsRunner = $this->createMock(InitialTestsRunner::class);
        $configuration = ConfigurationBuilder::withMinimalTestData()->withSkipCoverage(true)->build();
        $this->fileSystemMock->expects($this->exactly(2))->method('isReadableFile')->willReturn(true);
        $this->fileSystemMock->expects($this->exactly(3))->method('dumpFile');
        $environment = $this->createMock(MemoryLimiterEnvironment::class);
        $environment->expects($this->once())->method('hasMemoryLimitSet')->willReturn(false);
        $memoryLimiter = new MemoryLimiter($environment);
        $processFactory = $this->createMock(MutantProcessContainerFactory::class);
        $processContainer = $this->createStub(MutantProcessContainer::class);
        $processFactory
            ->expects($this->once())
            ->method('create')
            ->willReturnCallback(function (array $command) use ($processContainer): MutantProcessContainer {
                $this->assertSame(['-d', 'memory_limit=25M'], array_slice($command, 1, 2));

                return $processContainer;
            })
        ;
        $adapter = $this->createAdapter(
            testFrameworkConfigContent: <<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <phpunit/>
                XML,
            memoryLimiter: $memoryLimiter,
            configuration: $configuration,
            initialTestsRunner: $initialTestsRunner,
            processFactory: $processFactory,
        );
        $initialTestsRunner
            ->expects($this->once())
            ->method('run')
            ->with(
                $adapter,
                [self::PHP_EXECUTABLE, '/path/to/phpunit', '--configuration', '/tmp/phpunitConfiguration.initial.infection.xml'],
                true,
            )
            ->willReturn($process)
        ;
        $adapter->executeInitialRun();

        $adapter->test(MutantBuilder::withMinimalTestData()->build());
    }

    public static function passOutputProvider(): iterable
    {
        yield ['OK, but incomplete, skipped, or risky tests!', true];

        yield ['OK (5 tests, 3 assertions)', true];

        yield ['FAILURES!', false];

        yield ['ERRORS!', false];

        yield ['No tests executed!', true];

        yield 'unrecognized output does not indicate a pass' => ['Unexpected output', false];

        yield 'warnings can accompany passing tests' => ['WARNINGS!', true];

        yield 'no tests output is case insensitive' => ['NO TESTS EXECUTED!', true];

        yield 'failures take precedence over warnings' => ['FAILURES! WARNINGS!', false];

        yield 'errors take precedence over warnings' => ['ERRORS! WARNINGS!', false];
    }

    public static function syntaxErrorOutputProvider(): iterable
    {
        yield ['OK, but incomplete, skipped, or risky tests!', false];

        yield ['ParseError: syntax error, unexpected ">"', true];

        yield 'syntax error output is case insensitive' => ['PARSEERROR: SYNTAX ERROR', true];
    }

    #[DataProvider('executionOrderProvider')]
    public function test_supports_execution_order_defects_random(bool $expected, string $version): void
    {
        $this->assertSame($expected, PhpUnitAdapter::supportsExecutionOrderDefectsRandom($version));
    }

    public static function executionOrderProvider(): iterable
    {
        yield [false, '10.0'];

        yield [false, '10.5.47'];

        yield [true, '10.5.48'];

        yield [true, '10.5.999'];

        yield [false, '11.0'];

        yield [false, '11.5.26'];

        yield [true, '11.5.27'];

        yield [true, '11.5.599'];

        yield [false, '12.0'];

        yield [false, '12.1'];

        yield [false, '12.2.6'];

        yield [true, '12.2.7'];

        yield [true, '12.2.99'];

        // because it's silently ignored https://github.com/infection/infection/pull/3458#discussion_r3743322156
        yield [false, '13.0'];

        yield [false, '13.2.99'];

        yield [false, '13.3'];

        yield [false, '14.0'];
    }

    public static function initialTestRunProvider(): iterable
    {
        $default = new InitialTestRunScenario(
            testFrameworkConfigContent: <<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <phpunit/>
                XML,
            version: self::DEFAULT_PHPUNIT_VERSION,
            filteredSourceFilesToMutate: [],
            executeOnlyCoveringTestCases: false,
            mapSourceClassToTestStrategy: null,
            extraOptions: '',
            phpExtraArgs: [],
            skipCoverage: false,
            pcovDirectory: '',
            expected: [
                self::PHP_EXECUTABLE,
                '/path/to/phpunit',
                '--configuration',
                '/tmp/phpunitConfiguration.initial.infection.xml',
                '--coverage-xml=/tmp/coverage-xml',
                '--log-junit=/tmp/infection/junit.xml',
            ],
        );

        yield 'default' => [$default];

        yield 'coverage retains source on PHPUnit 11.5' => [$default->withVersion('11.5.599')];

        yield 'coverage retains source on PHPUnit 12.0' => [$default->withVersion('12.0')];

        yield 'coverage excludes source on PHPUnit 13.0' => [
            $default
                ->withVersion('13.0')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--exclude-source-from-xml-coverage',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with extra PHP arguments' => [
            $default
                ->withPhpExtraArgs([
                    '-dxdebug.mode=coverage',
                    '-d',
                    'memory_limit=-1',
                ])
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '-dxdebug.mode=coverage',
                    '-d',
                    'memory_limit=-1',
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with extra PHPUnit options' => [
            $default
                ->withExtraOptions('--group=default --filter="Mailer"')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--group=default',
                    '--filter=Mailer',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with extra PHPUnit args' => [
            $default
                ->withExtraArgs('--group=default --filter="Mailer"')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--group=default',
                    '--filter=Mailer',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        // Correctness is ensured upstream – within reason; we can't guard against all bad input
        yield 'with extra PHPUnit options missing the leading dashes' => [
            $default
                ->withExtraOptions('group=default filter="Mailer"')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    'group=default',
                    'filter=Mailer',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with extra PHPUnit args missing the leading dashes' => [
            $default
                ->withExtraArgs('group=default filter="Mailer"')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    'group=default',
                    'filter=Mailer',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with short extra PHPUnit option' => [
            $default
                ->withExtraOptions('-v --group=default')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '-v',
                    '--group=default',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with short extra PHPUnit arg' => [
            $default
                ->withExtraArgs('-v --group=default')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '-v',
                    '--group=default',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with option value separated by a space' => [
            $default
                ->withExtraOptions('--filter "a test with spaces"')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--filter',
                    'a test with spaces',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with arg value separated by a space' => [
            $default
                ->withExtraArgs('--filter "a test with spaces"')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--filter',
                    'a test with spaces',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with option value requiring shell escaping' => [
            $default
                ->withExtraOptions('--filter="a test with spaces"')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--filter=a test with spaces',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with arg value requiring shell escaping' => [
            $default
                ->withExtraArgs('--filter="a test with spaces"')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--filter=a test with spaces',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with single-quoted option value requiring shell escaping' => [
            $default
                ->withExtraOptions("--filter='a test with spaces'")
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--filter=a test with spaces',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with single-quoted arg value requiring shell escaping' => [
            $default
                ->withExtraArgs("--filter='a test with spaces'")
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--filter=a test with spaces',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with option value containing option-like text' => [
            $default
                ->withExtraOptions('--filter="a test -- with option-like text" --group=default')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--filter=a test -- with option-like text',
                    '--group=default',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with arg value containing arg-like text' => [
            $default
                ->withExtraArgs('--filter="a test -- with option-like text" --group=default')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--filter=a test -- with option-like text',
                    '--group=default',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with positional test file argument' => [
            $default
                ->withExtraOptions('tests/FooTest.php --filter=Foo')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    'tests/FooTest.php',
                    '--filter=Foo',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with positional test file argument with extra args' => [
            $default
                ->withExtraArgs('tests/FooTest.php --filter=Foo')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    'tests/FooTest.php',
                    '--filter=Foo',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with positional test file argument requiring shell escaping' => [
            $default
                ->withExtraOptions('"tests/Foo Test.php"')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    'tests/Foo Test.php',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with positional test file argument requiring shell escaping with extra args' => [
            $default
                ->withExtraArgs('"tests/Foo Test.php"')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    'tests/Foo Test.php',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'without generated coverage options when coverage is skipped' => [
            $default
                ->withExtraOptions('')
                ->withSkipCoverage(true)
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                ]),
        ];

        yield 'without generated coverage args when coverage is skipped' => [
            $default
                ->withExtraArgs('')
                ->withSkipCoverage(true)
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                ]),
        ];

        yield 'with coverage report excluding source for PHPUnit 12.5 and above' => [
            $default
                ->withVersion('12.5')
                ->withExtraOptions('--group=default')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--group=default',
                    '--exclude-source-from-xml-coverage',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with coverage report excluding source for PHPUnit 12.5 and above with extra args' => [
            $default
                ->withVersion('12.5')
                ->withExtraArgs('--group=default')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--group=default',
                    '--exclude-source-from-xml-coverage',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with PCOV directory' => [
            $default
                ->withPhpExtraArgs(['-d', 'memory_limit=-1'])
                ->withPcovDirectory('.')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '-d',
                    'memory_limit=-1',
                    '-d',
                    'pcov.directory=.',
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with filtered source files and class-to-test mapping' => [
            $default
                ->withFilteredSourceFilesToMutate([
                    new SplFileInfo('src/Foo.php'),
                    new SplFileInfo('src/bar/Baz.php'),
                ])
                ->withMapSourceClassToTestStrategy(MapSourceClassToTestStrategy::SIMPLE)
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                    '--filter',
                    'FooTest|BazTest',
                ]),
        ];

        yield 'with filtered source files but no class-to-test mapping' => [
            $default
                ->withFilteredSourceFilesToMutate([
                    new SplFileInfo('src/Foo.php'),
                    new SplFileInfo('src/bar/Baz.php'),
                ])
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with filtered source files and exact filter option' => [
            $default
                ->withFilteredSourceFilesToMutate([
                    new SplFileInfo('src/Foo.php'),
                    new SplFileInfo('src/bar/Baz.php'),
                ])
                ->withMapSourceClassToTestStrategy(MapSourceClassToTestStrategy::SIMPLE)
                ->withExtraOptions('--filter --group=default')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--filter',
                    '--group=default',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with filtered source files and exact filter arg' => [
            $default
                ->withFilteredSourceFilesToMutate([
                    new SplFileInfo('src/Foo.php'),
                    new SplFileInfo('src/bar/Baz.php'),
                ])
                ->withMapSourceClassToTestStrategy(MapSourceClassToTestStrategy::SIMPLE)
                ->withExtraArgs('--filter --group=default')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--filter',
                    '--group=default',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with filtered source files and separated filter option value' => [
            $default
                ->withFilteredSourceFilesToMutate([
                    new SplFileInfo('src/Foo.php'),
                    new SplFileInfo('src/bar/Baz.php'),
                ])
                ->withMapSourceClassToTestStrategy(MapSourceClassToTestStrategy::SIMPLE)
                ->withExtraOptions('--filter Foo')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--filter',
                    'Foo',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with filtered source files and separated filter arg value' => [
            $default
                ->withFilteredSourceFilesToMutate([
                    new SplFileInfo('src/Foo.php'),
                    new SplFileInfo('src/bar/Baz.php'),
                ])
                ->withMapSourceClassToTestStrategy(MapSourceClassToTestStrategy::SIMPLE)
                ->withExtraArgs('--filter Foo')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--filter',
                    'Foo',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with filtered source files and equals filter option value' => [
            $default
                ->withFilteredSourceFilesToMutate([
                    new SplFileInfo('src/Foo.php'),
                    new SplFileInfo('src/bar/Baz.php'),
                ])
                ->withMapSourceClassToTestStrategy(MapSourceClassToTestStrategy::SIMPLE)
                ->withExtraOptions('--filter=Foo')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    // Incorrect current behaviour: "--filter=Foo" does not prevent the generated filter from being appended.
                    '--filter=Foo',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                    '--filter',
                    'FooTest|BazTest',
                ]),
        ];

        yield 'with filtered source files and equals filter arg value' => [
            $default
                ->withFilteredSourceFilesToMutate([
                    new SplFileInfo('src/Foo.php'),
                    new SplFileInfo('src/bar/Baz.php'),
                ])
                ->withMapSourceClassToTestStrategy(MapSourceClassToTestStrategy::SIMPLE)
                ->withExtraArgs('--filter=Foo')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    // Incorrect current behaviour: "--filter=Foo" does not prevent the generated filter from being appended.
                    '--filter=Foo',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                    '--filter',
                    'FooTest|BazTest',
                ]),
        ];

        yield 'with testsuite option' => [
            $default
                ->withExtraOptions('--testsuite=unit')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--testsuite=unit',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with testsuite arg' => [
            $default
                ->withExtraArgs('--testsuite=unit')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--testsuite=unit',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with testsuite option value requiring shell escaping' => [
            $default
                ->withExtraOptions('--testsuite "Unit Tests"')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--testsuite',
                    'Unit Tests',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with testsuite arg value requiring shell escaping' => [
            $default
                ->withExtraArgs('--testsuite "Unit Tests"')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--testsuite',
                    'Unit Tests',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with configuration option' => [
            $default
                ->withExtraOptions('--configuration=custom-phpunit.xml')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--configuration=custom-phpunit.xml',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with configuration arg' => [
            $default
                ->withExtraArgs('--configuration=custom-phpunit.xml')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--configuration=custom-phpunit.xml',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with configuration option value separated by a space' => [
            $default
                ->withExtraOptions('--configuration custom-phpunit.xml')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--configuration',
                    'custom-phpunit.xml',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with configuration arg value separated by a space' => [
            $default
                ->withExtraArgs('--configuration custom-phpunit.xml')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--configuration',
                    'custom-phpunit.xml',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with empty extra PHP arguments' => [
            $default
                ->withPhpExtraArgs([
                    '',
                    '-d',
                    '',
                    'memory_limit=-1',
                ])
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '-d',
                    'memory_limit=-1',
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with skipped coverage and PCOV directory' => [
            $default
                ->withSkipCoverage(true)
                ->withPcovDirectory('.')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                ]),
        ];

        yield 'with skipped coverage for PHPUnit 12.5 and above' => [
            $default
                ->withVersion('12.5')
                ->withSkipCoverage(true)
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                ]),
        ];

        yield 'with PCOV directory containing spaces' => [
            $default
                ->withPcovDirectory('/path with spaces/src')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '-d',
                    'pcov.directory=/path with spaces/src',
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with path option containing spaces' => [
            $default
                ->withExtraOptions('--path=/a path/with spaces')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--path=/a',
                    'path/with',
                    'spaces',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with path arg containing spaces' => [
            $default
                ->withExtraArgs('--path=/a path/with spaces')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--path=/a',
                    'path/with',
                    'spaces',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with repeated spaces between extra options' => [
            $default
                ->withExtraOptions('--group=default  --filter=Foo')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--group=default',
                    '--filter=Foo',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with repeated spaces between extra args' => [
            $default
                ->withExtraArgs('--group=default  --filter=Foo')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--group=default',
                    '--filter=Foo',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with newline between extra options' => [
            $default
                ->withExtraOptions("--group=default\n--filter=Foo")
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--group=default',
                    '--filter=Foo',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with newline between extra args' => [
            $default
                ->withExtraArgs("--group=default\n--filter=Foo")
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--group=default',
                    '--filter=Foo',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with tab between extra options' => [
            $default
                ->withExtraOptions("--group=default\t--filter=Foo")
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--group=default',
                    '--filter=Foo',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];

        yield 'with tab between extra args' => [
            $default
                ->withExtraArgs("--group=default\t--filter=Foo")
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.initial.infection.xml',
                    '--group=default',
                    '--filter=Foo',
                    '--coverage-xml=/tmp/coverage-xml',
                    '--log-junit=/tmp/infection/junit.xml',
                ]),
        ];
    }

    public static function mutantCommandLineProvider(): iterable
    {
        $default = new MutantCommandLineScenario(
            testFrameworkConfigContent: <<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <phpunit/>
                XML,
            version: self::DEFAULT_PHPUNIT_VERSION,
            executeOnlyCoveringTestCases: false,
            coverageTests: [
                new TestLocation('App\ServiceTest::test_case1', '/path/to/tests/ServiceTest.php', 0.1),
            ],
            mutatedFilePath: '/tmp/mutant.php',
            mutationHash: 'mutation-hash',
            mutationOriginalFilePath: '/path/to/project/src/Service.php',
            extraOptions: '',
            expected: [
                self::PHP_EXECUTABLE,
                '/path/to/phpunit',
                '--configuration',
                '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
            ],
        );

        yield 'default' => [$default];

        yield 'with extra PHPUnit options' => [
            $default
                ->withExtraOptions('--group=default --filter="Mailer"')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                    '--group=default',
                ]),
        ];

        yield 'with extra PHPUnit args' => [
            $default
                ->withExtraArgs('--group=default --filter="Mailer"')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                    '--group=default',
                ]),
        ];

        yield 'with short extra PHPUnit option' => [
            $default
                ->withExtraOptions('-v --group=default')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                    '-v',
                    '--group=default',
                ]),
        ];

        yield 'with short extra PHPUnit arg' => [
            $default
                ->withExtraArgs('-v --group=default')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                    '-v',
                    '--group=default',
                ]),
        ];

        yield 'with option value separated by a space' => [
            $default
                ->withExtraOptions('--group "a test with spaces"')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                    '--group',
                    'a test with spaces',
                ]),
        ];

        yield 'with arg value separated by a space' => [
            $default
                ->withExtraArgs('--group "a test with spaces"')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                    '--group',
                    'a test with spaces',
                ]),
        ];

        yield 'with option value containing option-like text' => [
            $default
                ->withExtraOptions('--exclude-group="a test -- with option-like text" --group=default')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                    '--exclude-group=a test -- with option-like text',
                    '--group=default',
                ]),
        ];

        yield 'with arg value containing arg-like text' => [
            $default
                ->withExtraArgs('--exclude-group="a test -- with option-like text" --group=default')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                    '--exclude-group=a test -- with option-like text',
                    '--group=default',
                ]),
        ];

        yield 'with positional test file argument' => [
            $default
                ->withExtraOptions('tests/FooTest.php --filter=Foo')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                    'tests/FooTest.php',
                ]),
        ];

        yield 'with positional test file argument with extra args' => [
            $default
                ->withExtraArgs('tests/FooTest.php --filter=Foo')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                    'tests/FooTest.php',
                ]),
        ];

        yield 'with configuration option' => [
            $default
                ->withExtraOptions('--configuration=custom-phpunit.xml')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                ]),
        ];

        yield 'with configuration arg' => [
            $default
                ->withExtraArgs('--configuration=custom-phpunit.xml')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                ]),
        ];

        yield 'with empty coverage tests and only covering test cases enabled' => [
            $default
                ->withExecuteOnlyCoveringTestCases(true)
                ->withCoverageTests([])
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                ]),
        ];

        yield 'with only covering test cases disabled' => [
            $default
                ->withExecuteOnlyCoveringTestCases(false)
                ->withCoverageTests([
                    new TestLocation('App\ServiceTest::test_case1', '/path/to/tests/ServiceTest.php', 0.1),
                ])
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                ]),
        ];

        yield 'with single covering test case' => [
            $default
                ->withExecuteOnlyCoveringTestCases(true)
                ->withCoverageTests([
                    new TestLocation('App\ServiceTest::test_case1', '/path/to/tests/ServiceTest.php', 0.1),
                ])
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                    '--filter',
                    '/ServiceTest\:\:test_case1/',
                ]),
        ];

        yield 'with multiple covering test cases' => [
            $default
                ->withExecuteOnlyCoveringTestCases(true)
                ->withCoverageTests([
                    new TestLocation('App\ServiceUnitTest::test_case1', '/path/to/tests/ServiceUnitTest.php', 0.1),
                    new TestLocation('App\ServiceUnitTest::test_case2', '/path/to/tests/ServiceUnitTest.php', 0.2),
                    new TestLocation('App\ServiceIntegrationTest::test_case1', '/path/to/tests/ServiceIntegrationTest.php', 0.3),
                ])
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                    '--filter',
                    '/ServiceUnitTest\:\:test_case1|ServiceUnitTest\:\:test_case2|ServiceIntegrationTest\:\:test_case1/',
                ]),
        ];

        yield 'with duplicate covering test cases' => [
            $default
                ->withExecuteOnlyCoveringTestCases(true)
                ->withCoverageTests([
                    new TestLocation('App\ServiceTest::test_case1', '/path/to/tests/ServiceTest.php', 0.1),
                    new TestLocation('App\ServiceTest::test_case1', '/path/to/tests/ServiceTest.php', 0.2),
                ])
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                    '--filter',
                    '/ServiceTest\:\:test_case1/',
                ]),
        ];

        yield 'with covering data provider test for PHPUnit 9' => [
            $default
                ->withExecuteOnlyCoveringTestCases(true)
                ->withCoverageTests([
                    new TestLocation('App\ServiceTest::test_case1 with data set "#1"', '/path/to/tests/ServiceTest.php', 0.1),
                ])
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                    '--filter',
                    '/ServiceTest\:\:test_case1 with data set "\#1"/',
                ]),
        ];

        yield 'with covering data provider test for PHPUnit 10' => [
            $default
                ->withVersion('10.1')
                ->withExecuteOnlyCoveringTestCases(true)
                ->withCoverageTests([
                    new TestLocation('App\ServiceTest::test_case1##1', '/path/to/tests/ServiceTest.php', 0.1),
                ])
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                    '--filter',
                    '/ServiceTest\:\:test_case1 with data set "\#1"/',
                ]),
        ];

        yield 'with covering test containing special characters' => [
            $default
                ->withExecuteOnlyCoveringTestCases(true)
                ->withCoverageTests([
                    new TestLocation('App\ServiceTest::test_case1 with data set "With special character >@&\::"', '/path/to/tests/ServiceTest.php', 0.1),
                    new TestLocation('App\ServiceTest::test_case2', '/path/to/tests/ServiceTest.php', 0.2),
                ])
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                    '--filter',
                    '/ServiceTest\:\:test_case1 with data set "With special character \>@&\\\\\:\:"|ServiceTest\:\:test_case2/',
                ]),
        ];

        yield 'with extra options and generated covering test filter' => [
            $default
                ->withExecuteOnlyCoveringTestCases(true)
                ->withExtraOptions('--group=default')
                ->withCoverageTests([
                    new TestLocation('App\ServiceTest::test_case1', '/path/to/tests/ServiceTest.php', 0.1),
                ])
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                    '--group=default',
                    '--filter',
                    '/ServiceTest\:\:test_case1/',
                ]),
        ];

        yield 'with extra args and generated covering test filter' => [
            $default
                ->withExecuteOnlyCoveringTestCases(true)
                ->withExtraArgs('--group=default')
                ->withCoverageTests([
                    new TestLocation('App\ServiceTest::test_case1', '/path/to/tests/ServiceTest.php', 0.1),
                ])
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                    '--group=default',
                    '--filter',
                    '/ServiceTest\:\:test_case1/',
                ]),
        ];

        yield 'with path option containing spaces' => [
            $default
                ->withExtraOptions('--path=/a path/with spaces')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                    '--path=/a',
                    'path/with',
                    'spaces',
                ]),
        ];

        yield 'with path arg containing spaces' => [
            $default
                ->withExtraArgs('--path=/a path/with spaces')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                    '--path=/a',
                    'path/with',
                    'spaces',
                ]),
        ];

        yield 'with repeated spaces between extra options' => [
            $default
                ->withExtraOptions('--group=default  --filter=Foo')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                    '--group=default',
                ]),
        ];

        yield 'with repeated spaces between extra args' => [
            $default
                ->withExtraArgs('--group=default  --filter=Foo')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                    '--group=default',
                ]),
        ];
    }

    private function classifyMutantOutput(string $output, int $exitCode): DetectionStatus
    {
        $mutant = MutantBuilder::withCompleteTestData()->build();
        $process = $this->createStub(Process::class);
        $process->method('isTerminated')->willReturn(true);
        $process->method('getOutput')->willReturn($output);
        $process->method('getExitCode')->willReturn($exitCode);
        $process->method('getStartTime')->willReturn(0.0);
        $processFactory = $this->createMock(MutantProcessContainerFactory::class);
        $processFactory
            ->expects($this->once())
            ->method('create')
            ->willReturnCallback(
                /**
                 * @param Closure(string): bool $testsPass
                 * @param (Closure(string): mixed)|null $isSyntaxError
                 */
                static function (array $_command, Mutant $mutant, Closure $testsPass, ?Closure $isSyntaxError) use ($process): MutantProcessContainer {
                    $mutantProcess = new MutantProcess(
                        $process,
                        $mutant,
                        new TestFrameworkMutantExecutionResultFactory($testsPass, $isSyntaxError),
                    );
                    $mutantProcess->markAsFinished();

                    return MutantProcessContainer::from(
                        static fn (): MutantProcess => $mutantProcess,
                    );
                },
            )
        ;
        $adapter = $this->createAdapter('<phpunit/>', processFactory: $processFactory);

        $result = $adapter->test($mutant)->getCurrent()->getMutantExecutionResult();

        return $result->getDetectionStatus();
    }

    /**
     * @param SplFileInfo[] $filteredSourceFilesToMutate
     * @param MapSourceClassToTestStrategy::*|null $mapSourceClassToTestStrategy
     */
    private function createAdapter(
        string $testFrameworkConfigContent,
        ?string $version = self::DEFAULT_PHPUNIT_VERSION,
        array $filteredSourceFilesToMutate = [],
        bool $executeOnlyCoveringTestCases = false,
        ?string $mapSourceClassToTestStrategy = null,
        ?ShellCommandRunner $shellCommandRunner = null,
        ?MemoryLimiter $memoryLimiter = null,
        ?Configuration $configuration = null,
        ?InitialTestsRunner $initialTestsRunner = null,
        ?MutantProcessContainerFactory $processFactory = null,
        ?PhpUnitLogger $logger = null,
    ): PhpUnitAdapter {
        $configuration ??= ConfigurationBuilder::withMinimalTestData()->build();
        $tmpDir = '/tmp';
        $projectDir = '/path/to/project';
        $testFrameworkConfigDir = '/path/to/project/tools/phpunit';
        $filteredSourceFilePathsToMutate = array_map(
            static fn (SplFileInfo $fileInfo): string => $fileInfo->getPathname(),
            $filteredSourceFilesToMutate,
        );

        $configManipulator = new XmlConfigurationManipulator(
            new PathReplacer(
                $this->fileSystemMock,
                $testFrameworkConfigDir,
            ),
            $testFrameworkConfigDir,
        );

        return new PhpUnitAdapter(
            '/path/to/phpunit',
            $tmpDir,
            '/tmp/infection/junit.xml',
            $this->pcovDirectoryProvider,
            new InitialConfigBuilder(
                $tmpDir,
                $testFrameworkConfigContent,
                $configManipulator,
                new XmlConfigurationVersionProvider(),
                $this->fileSystemMock,
                ['bin', 'src'],
                $filteredSourceFilePathsToMutate,
            ),
            new MutationConfigBuilder(
                $tmpDir,
                $testFrameworkConfigContent,
                $configManipulator,
                $projectDir,
                new TestRunOrderResolver(),
                $this->fileSystemMock,
            ),
            new ArgumentsAndOptionsBuilder(
                $executeOnlyCoveringTestCases,
                $filteredSourceFilesToMutate,
                $mapSourceClassToTestStrategy,
                false,
            ),
            $shellCommandRunner ?? $this->createStub(ShellCommandRunner::class),
            new VersionParser('PHPUnit'),    // won't be used since we pass the version
            new CommandLineBuilder($this->phpExecutableFinderMock),
            '/path/to/project/tools/phpunit/phpunit.xml',
            $logger ?? $this->createStub(PhpUnitLogger::class),
            new CoverageCheckerFactory(
                $configuration,
                JUnitReportLocator::create(
                    $this->fileSystemMock,
                    '',
                ),
                IndexXmlCoverageLocator::create(
                    $this->fileSystemMock,
                    '',
                ),
            ),
            $initialTestsRunner ?? $this->createStub(InitialTestsRunner::class),
            $configuration,
            $processFactory ?? $this->createStub(MutantProcessContainerFactory::class),
            new TestFrameworkExtraOptionsFilter(),
            $memoryLimiter ?? new MemoryLimiter($this->createStub(MemoryLimiterEnvironment::class)),
            $version,
        );
    }
}
