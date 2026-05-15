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
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\Config\ValueProvider\PCOVDirectoryProvider;
use Infection\FileSystem\FileSystem;
use Infection\Framework\OperatingSystem;
use Infection\TestFramework\CommandLineBuilder;
use Infection\TestFramework\MapSourceClassToTestStrategy;
use Infection\TestFramework\PhpUnit\Adapter\PhpUnitAdapter;
use Infection\TestFramework\PhpUnit\CommandLine\ArgumentsAndOptionsBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\InitialConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\MutationConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationManipulator;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationVersionProvider;
use Infection\TestFramework\Tracing\TestRunOrderResolver;
use Infection\TestFramework\VersionParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use Symfony\Component\Process\PhpExecutableFinder;

#[CoversClass(PhpUnitAdapter::class)]
final class PhpUnitAdapterTest extends TestCase
{
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
            ->willReturn(self::PHP_EXECUTABLE);

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
            ->method('dumpFile');
        $this->pcovDirectoryProvider
            ->expects($this->never())
            ->method('shallProvide');

        $this->assertSame('PHPUnit', $this->adapter->getName());
    }

    public function test_it_supports_junit_reports(): void
    {
        $this->fileSystemMock
            ->expects($this->never())
            ->method('dumpFile');
        $this->pcovDirectoryProvider
            ->expects($this->never())
            ->method('shallProvide');

        $this->assertTrue($this->adapter->hasJUnitReport());
    }

    #[DataProvider('passOutputProvider')]
    public function test_it_can_tell_if_tests_pass_from_the_output(
        string $output,
        bool $expected,
    ): void {
        $this->fileSystemMock
            ->expects($this->never())
            ->method('dumpFile');
        $this->pcovDirectoryProvider
            ->expects($this->never())
            ->method('shallProvide');

        $actual = $this->adapter->testsPass($output);

        $this->assertSame($expected, $actual);
    }

    #[DataProvider('syntaxErrorOutputProvider')]
    public function test_it_can_tell_if_there_is_a_syntax_error_from_the_output(
        string $output,
        bool $expected,
    ): void {
        $this->fileSystemMock
            ->expects($this->never())
            ->method('dumpFile');
        $this->pcovDirectoryProvider
            ->expects($this->never())
            ->method('shallProvide');

        $actual = $this->adapter->isSyntaxError($output);

        $this->assertSame($expected, $actual);
    }

    #[DataProvider('memoryReportProvider')]
    public function test_it_can_tell_the_memory_usage_from_the_output(
        string $output,
        float $expectedResult,
    ): void {
        $this->fileSystemMock
            ->expects($this->never())
            ->method('dumpFile');
        $this->pcovDirectoryProvider
            ->expects($this->never())
            ->method('shallProvide');

        $result = $this->adapter->getMemoryUsed($output);

        $this->assertSame($expectedResult, $result);
    }

    public function test_it_provides_initial_run_only_options(): void
    {
        $this->fileSystemMock
            ->expects($this->never())
            ->method('dumpFile');
        $this->pcovDirectoryProvider
            ->expects($this->never())
            ->method('shallProvide');

        $options = $this->adapter->getInitialRunOnlyOptions();

        $this->assertSame(
            ['--configuration', '--filter', '--testsuite'],
            $options,
        );
    }

    #[DataProvider('initialTestRunProvider')]
    public function test_it_provides_initial_test_run_command_line(
        InitialTestRunScenario $scenario,
    ): void {
        $this->fileSystemMock
            ->expects($this->once())
            // Checking the content of the dumped XML is out of the scope of this test.
            // It is doable, but would be unnecessarily verbose.
            ->method('dumpFile');

        $this->pcovDirectoryProvider
            ->expects($scenario->skipCoverage ? $this->never() : $this->once())
            ->method('shallProvide')
            ->willReturn($scenario->pcovDirectory !== '');
        $this->pcovDirectoryProvider
            ->expects($this->atMost(1))
            ->method('getDirectory')
            ->willReturn($scenario->pcovDirectory);

        $adapter = $this->createAdapter(
            testFrameworkConfigContent: $scenario->testFrameworkConfigContent,
            version: $scenario->version,
            filteredSourceFilesToMutate: $scenario->filteredSourceFilesToMutate,
            executeOnlyCoveringTestCases: $scenario->executeOnlyCoveringTestCases,
            mapSourceClassToTestStrategy: $scenario->mapSourceClassToTestStrategy,
        );

        $actual = $adapter->getInitialTestRunCommandLine(
            extraOptions: $scenario->extraOptions,
            phpExtraArgs: $scenario->phpExtraArgs,
            skipCoverage: $scenario->skipCoverage,
        );

        $this->assertSame(
            $scenario->expected,
            $actual,
        );
    }

    #[DataProvider('mutantCommandLineProvider')]
    public function test_it_provides_mutant_command_line(
        MutantCommandLineScenario $scenario,
    ): void {
        $this->fileSystemMock
            ->expects($this->exactly(2))
            // Checking the content of the dumped XML and generated autoload file is out of the scope of this test.
            ->method('dumpFile');

        $this->pcovDirectoryProvider
            ->expects($this->never())
            ->method('shallProvide');

        $adapter = $this->createAdapter(
            testFrameworkConfigContent: $scenario->testFrameworkConfigContent,
            version: $scenario->version,
            executeOnlyCoveringTestCases: $scenario->executeOnlyCoveringTestCases,
        );

        $actual = $adapter->getMutantCommandLine(
            coverageTests: $scenario->coverageTests,
            mutatedFilePath: $scenario->mutatedFilePath,
            mutationHash: $scenario->mutationHash,
            mutationOriginalFilePath: $scenario->mutationOriginalFilePath,
            extraOptions: $scenario->extraOptions,
        );

        $this->assertSame(
            $scenario->expected,
            $actual,
        );
    }

    public static function passOutputProvider(): iterable
    {
        yield ['OK, but incomplete, skipped, or risky tests!', true];

        yield ['OK (5 tests, 3 assertions)', true];

        yield ['FAILURES!', false];

        yield ['ERRORS!', false];

        yield ['No tests executed!', true];
    }

    public static function syntaxErrorOutputProvider(): iterable
    {
        yield ['OK, but incomplete, skipped, or risky tests!', false];

        yield ['ParseError: syntax error, unexpected ">"', true];
    }

    public static function memoryReportProvider(): iterable
    {
        yield ['Memory: 8.00MB', 8.0];

        yield ['Memory: 68.00MB', 68.0];

        yield ['Memory: 68.00 MB', 68.0];

        yield ['Time: 2.51 seconds', -1.0];
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

        yield [true, '13.0'];
    }

    #[DataProvider('coverageWithoutSourceProvider')]
    public function test_supports_coverage_without_source(string $version, bool $expected): void
    {
        $this->assertSame($expected, PhpUnitAdapter::supportsExcludingSourceFromCoverage($version));
    }

    public static function coverageWithoutSourceProvider(): iterable
    {
        yield ['11.5.599', false];

        yield ['12.0', false];

        yield ['12.5', true];

        yield ['13.0', true];
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
                    OperatingSystem::isWindows()
                        ? 'pcov.directory="."'
                        : "pcov.directory='.'",
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

        yield 'with PCOV directory requiring shell escaping' => [
            $default
                ->withPcovDirectory('/path with spaces/src')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '-d',
                    OperatingSystem::isWindows()
                        ? 'pcov.directory="/path with spaces/src"'
                        : "pcov.directory='/path with spaces/src'",
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
                    '--filter=Mailer',
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
                    '--filter=Mailer',
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
                ->withExtraOptions('--filter "a test with spaces"')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                    '--filter',
                    'a test with spaces',
                ]),
        ];

        yield 'with arg value separated by a space' => [
            $default
                ->withExtraArgs('--filter "a test with spaces"')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                    '--filter',
                    'a test with spaces',
                ]),
        ];

        yield 'with option value containing option-like text' => [
            $default
                ->withExtraOptions('--filter="a test -- with option-like text" --group=default')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                    '--filter=a test -- with option-like text',
                    '--group=default',
                ]),
        ];

        yield 'with arg value containing arg-like text' => [
            $default
                ->withExtraArgs('--filter="a test -- with option-like text" --group=default')
                ->withExpected([
                    self::PHP_EXECUTABLE,
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.mutation-hash.infection.xml',
                    '--filter=a test -- with option-like text',
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
                    '--filter=Foo',
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
                    '--filter=Foo',
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
                    '--configuration=custom-phpunit.xml',
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
                    '--configuration=custom-phpunit.xml',
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
                    '--filter=Foo',
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
                    '--filter=Foo',
                ]),
        ];
    }

    /**
     * @param SplFileInfo[] $filteredSourceFilesToMutate
     * @param MapSourceClassToTestStrategy::*|null $mapSourceClassToTestStrategy
     */
    private function createAdapter(
        string $testFrameworkConfigContent,
        string $version = self::DEFAULT_PHPUNIT_VERSION,
        array $filteredSourceFilesToMutate = [],
        bool $executeOnlyCoveringTestCases = false,
        ?string $mapSourceClassToTestStrategy = null,
    ): PhpUnitAdapter {
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
            ),
            new VersionParser(),    // won't be used since we pass the version
            new CommandLineBuilder($this->phpExecutableFinderMock),
            $version,
        );
    }
}
