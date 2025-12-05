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

namespace Infection\Tests\TestFramework\PhpUnit\Adapter;

use Infection\Config\ValueProvider\PCOVDirectoryProvider;
use Infection\Framework\OperatingSystem;
use Infection\TestFramework\CommandLineArgumentsAndOptionsBuilder;
use Infection\TestFramework\CommandLineBuilder;
use Infection\TestFramework\PhpUnit\Adapter\PhpUnitAdapter;
use Infection\TestFramework\PhpUnit\Config\Builder\InitialConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\MutationConfigBuilder;
use Infection\TestFramework\VersionParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(PhpUnitAdapter::class)]
final class PhpUnitAdapterTest extends TestCase
{
    private PhpUnitAdapter $adapter;

    private MockObject&PCOVDirectoryProvider $pcovDirectoryProvider;

    private MockObject&CommandLineArgumentsAndOptionsBuilder $cliArgumentsBuilder;

    private MockObject&CommandLineBuilder $commandLineBuilder;

    protected function setUp(): void
    {
        $this->pcovDirectoryProvider = $this->createMock(PCOVDirectoryProvider::class);
        $this->cliArgumentsBuilder = $this->createMock(CommandLineArgumentsAndOptionsBuilder::class);
        $this->commandLineBuilder = $this->createMock(CommandLineBuilder::class);

        $this->adapter = $this->getPHPUnitAdapter();
    }

    public function test_it_has_a_name(): void
    {
        $this->assertSame('PHPUnit', $this->adapter->getName());
    }

    public function test_it_supports_junit_reports(): void
    {
        $this->assertTrue($this->adapter->hasJUnitReport());
    }

    #[DataProvider('passOutputProvider')]
    public function test_it_can_tell_if_tests_pass_from_the_output(
        string $output,
        bool $expected,
    ): void {
        $actual = $this->adapter->testsPass($output);

        $this->assertSame($expected, $actual);
    }

    #[DataProvider('syntaxErrorOutputProvider')]
    public function test_it_can_tell_if_there_is_a_syntax_error_from_the_output(
        string $output,
        bool $expected,
    ): void {
        $actual = $this->adapter->isSyntaxError($output);

        $this->assertSame($expected, $actual);
    }

    #[DataProvider('memoryReportProvider')]
    public function test_it_can_tell_the_memory_usage_from_the_output(string $output, float $expectedResult): void
    {
        $result = $this->adapter->getMemoryUsed($output);

        $this->assertSame($expectedResult, $result);
    }

    public function test_it_provides_initial_run_only_options(): void
    {
        $options = $this->adapter->getInitialRunOnlyOptions();

        $this->assertSame(
            ['--configuration', '--filter', '--testsuite'],
            $options,
        );
    }

    #[Group('integration')]
    public function test_it_provides_initial_test_run_command_line_when_no_coverage_is_expected(): void
    {
        $this->cliArgumentsBuilder
            ->expects($this->once())
            ->method('buildForInitialTestsRun')
            ->with('', '--group=default')
        ;

        $this->commandLineBuilder
            ->expects($this->once())
            ->method('build')
            ->with('/path/to/phpunit', ['-d', 'memory_limit=-1'], [])
            ->willReturn(['/path/to/phpunit', '--dummy-argument'])
        ;

        $this->pcovDirectoryProvider
            ->expects($this->never())
            ->method($this->anything())
        ;

        $initialTestRunCommandLine = $this->adapter->getInitialTestRunCommandLine('--group=default', ['-d', 'memory_limit=-1'], true);

        $this->assertSame(
            [
                '/path/to/phpunit',
                '--dummy-argument',
            ],
            $initialTestRunCommandLine,
        );
    }

    #[Group('integration')]
    public function test_it_provides_initial_test_run_command_line_when_coverage_report_is_requested(): void
    {
        $this->cliArgumentsBuilder
            ->expects($this->once())
            ->method('buildForInitialTestsRun')
            ->with('', '--group=default --coverage-xml=/tmp/coverage-xml --log-junit=/tmp/infection/junit.xml')
            ->willReturn([
                '--group=default', '--coverage-xml=/tmp/coverage-xml', '--log-junit=/tmp/infection/junit.xml',
            ])
        ;

        $this->commandLineBuilder
            ->expects($this->once())
            ->method('build')
            ->with('/path/to/phpunit', ['-d', 'memory_limit=-1'], [
                '--group=default', '--coverage-xml=/tmp/coverage-xml', '--log-junit=/tmp/infection/junit.xml',
            ])
            ->willReturn([
                '/path/to/phpunit',
                '--group=default',
                '--coverage-xml=/tmp/coverage-xml',
                '--log-junit=/tmp/infection/junit.xml',
            ])
        ;

        $this->pcovDirectoryProvider
            ->expects($this->once())
            ->method('shallProvide')
            ->willReturn(false)
        ;

        $this->pcovDirectoryProvider
            ->expects($this->never())
            ->method('getDirectory')
        ;

        $initialTestRunCommandLine = $this->adapter->getInitialTestRunCommandLine('--group=default', ['-d', 'memory_limit=-1'], false);

        $this->assertSame(
            [
                '/path/to/phpunit',
                '--group=default',
                '--coverage-xml=/tmp/coverage-xml',
                '--log-junit=/tmp/infection/junit.xml',
            ],
            $initialTestRunCommandLine,
        );
    }

    #[Group('integration')]
    public function test_it_provides_initial_test_run_command_line_with_fast_path_when_coverage_report_is_requested(): void
    {
        $this->adapter = $this->getPHPUnitAdapter('12.5');

        $this->cliArgumentsBuilder
            ->expects($this->once())
            ->method('buildForInitialTestsRun')
            ->with('', '--group=default --exclude-source-from-xml-coverage --coverage-xml=/tmp/coverage-xml --log-junit=/tmp/infection/junit.xml')
            ->willReturn([
                '--group=default', '--exclude-source-from-xml-coverage --coverage-xml=/tmp/coverage-xml', '--log-junit=/tmp/infection/junit.xml',
            ])
        ;

        $this->commandLineBuilder
            ->expects($this->once())
            ->method('build')
            ->with('/path/to/phpunit', ['-d', 'memory_limit=-1'], [
                '--group=default', '--exclude-source-from-xml-coverage --coverage-xml=/tmp/coverage-xml', '--log-junit=/tmp/infection/junit.xml',
            ])
            ->willReturn([
                '/path/to/phpunit',
                '--group=default',
                '--exclude-source-from-xml-coverage',
                '--coverage-xml=/tmp/coverage-xml',
                '--log-junit=/tmp/infection/junit.xml',
            ])
        ;

        $this->pcovDirectoryProvider
            ->expects($this->once())
            ->method('shallProvide')
            ->willReturn(false)
        ;

        $this->pcovDirectoryProvider
            ->expects($this->never())
            ->method('getDirectory')
        ;

        $initialTestRunCommandLine = $this->adapter->getInitialTestRunCommandLine('--group=default', ['-d', 'memory_limit=-1'], false);

        $this->assertSame(
            [
                '/path/to/phpunit',
                '--group=default',
                '--exclude-source-from-xml-coverage',
                '--coverage-xml=/tmp/coverage-xml',
                '--log-junit=/tmp/infection/junit.xml',
            ],
            $initialTestRunCommandLine,
        );
    }

    #[Group('integration')]
    public function test_it_provides_initial_test_run_command_line_when_coverage_report_is_requested_and_pcov_is_in_use(): void
    {
        $this->cliArgumentsBuilder
            ->expects($this->once())
            ->method('buildForInitialTestsRun')
            ->with('', '--group=default --coverage-xml=/tmp/coverage-xml --log-junit=/tmp/infection/junit.xml')
            ->willReturn([
                '--group=default', '--coverage-xml=/tmp/coverage-xml', '--log-junit=/tmp/infection/junit.xml',
            ])
        ;

        $this->commandLineBuilder
            ->expects($this->once())
            ->method('build')
            ->with('/path/to/phpunit', [
                '-d',
                'memory_limit=-1',
                '-d',
                OperatingSystem::isWindows() ? 'pcov.directory="."' : "pcov.directory='.'",
            ], [
                '--group=default', '--coverage-xml=/tmp/coverage-xml', '--log-junit=/tmp/infection/junit.xml',
            ])
            ->willReturn([
                '/path/to/phpunit',
                '--group=default',
                '--coverage-xml=/tmp/coverage-xml',
                '--log-junit=/tmp/infection/junit.xml',
            ])
        ;

        $this->pcovDirectoryProvider
            ->expects($this->once())
            ->method('shallProvide')
            ->willReturn(true)
        ;

        $this->pcovDirectoryProvider
            ->expects($this->once())
            ->method('getDirectory')
            ->willReturn('.')
        ;

        $initialTestRunCommandLine = $this->adapter->getInitialTestRunCommandLine('--group=default', ['-d', 'memory_limit=-1'], false);

        $this->assertSame(
            [
                '/path/to/phpunit',
                '--group=default',
                '--coverage-xml=/tmp/coverage-xml',
                '--log-junit=/tmp/infection/junit.xml',
            ],
            $initialTestRunCommandLine,
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

    private function getPHPUnitAdapter(string $version = '9.0'): PhpUnitAdapter
    {
        return new PhpUnitAdapter(
            '/path/to/phpunit',
            '/tmp',
            '/tmp/infection/junit.xml',
            $this->pcovDirectoryProvider,
            $this->createMock(InitialConfigBuilder::class),
            $this->createMock(MutationConfigBuilder::class),
            $this->cliArgumentsBuilder,
            new VersionParser(),
            $this->commandLineBuilder,
            $version,
        );
    }
}
