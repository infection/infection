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

use function array_map;
use Infection\PhpParser\Visitor\IgnoreNode\PhpUnitCodeCoverageAnnotationIgnorer;
use Infection\TestFramework\CommandLineArgumentsAndOptionsBuilder;
use Infection\TestFramework\CommandLineBuilder;
use Infection\TestFramework\PhpUnit\Adapter\PhpUnitAdapter;
use Infection\TestFramework\PhpUnit\Config\Builder\InitialConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\MutationConfigBuilder;
use Infection\TestFramework\VersionParser;
use PHPUnit\Framework\TestCase;

final class PhpUnitAdapterTest extends TestCase
{
    /**
     * @var PhpUnitAdapter
     */
    private $adapter;

    private $initialConfigBuilder;
    private $mutationConfigBuilder;
    private $cliArgumentsBuilder;
    private $commandLineBuilder;

    protected function setUp(): void
    {
        $this->initialConfigBuilder = $this->createMock(InitialConfigBuilder::class);
        $this->mutationConfigBuilder = $this->createMock(MutationConfigBuilder::class);
        $this->cliArgumentsBuilder = $this->createMock(CommandLineArgumentsAndOptionsBuilder::class);
        $this->commandLineBuilder = $this->createMock(CommandLineBuilder::class);

        $this->adapter = new PhpUnitAdapter(
            '/path/to/phpunit',
            '/tmp',
            '/tmp/infection/junit.xml',
            $this->initialConfigBuilder,
            $this->mutationConfigBuilder,
            $this->cliArgumentsBuilder,
            new VersionParser(),
            $this->commandLineBuilder,
            '9.0'
        );
    }

    public function test_it_has_a_name(): void
    {
        $this->assertSame('PHPUnit', $this->adapter->getName());
    }

    public function test_it_supports_junit_reports(): void
    {
        $this->assertTrue($this->adapter->hasJUnitReport());
    }

    /**
     * @dataProvider outputProvider
     */
    public function test_it_can_tell_the_outcome_of_the_tests_from_the_output(
        string $output,
        bool $expected
    ): void {
        $actual = $this->adapter->testsPass($output);

        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider memoryReportProvider
     */
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
            $options
        );
    }

    public function test_it_provides_node_ignorers(): void
    {
        $nodeIgnorers = array_map('get_class', $this->adapter->getNodeIgnorers());

        $this->assertSame(
            [PhpUnitCodeCoverageAnnotationIgnorer::class],
            $nodeIgnorers
        );
    }

    /**
     * @group integration
     */
    public function test_it_provides_initial_test_run_command_line_when_no_coverage_is_expected(): void
    {
        $this->cliArgumentsBuilder
            ->expects($this->once())
            ->method('build')
            ->with('', '--group=default')
        ;

        $this->commandLineBuilder
            ->expects($this->once())
            ->method('build')
            ->with('/path/to/phpunit', ['-d', 'memory_limit=-1'], [])
            ->willReturn(['/path/to/phpunit', '--dummy-argument'])
        ;

        $initialTestRunCommandLine = $this->adapter->getInitialTestRunCommandLine('--group=default', ['-d', 'memory_limit=-1'], true);

        $this->assertSame(
            [
                '/path/to/phpunit',
                '--dummy-argument',
            ],
            $initialTestRunCommandLine
        );
    }

    /**
     * @group integration
     */
    public function test_it_provides_initial_test_run_command_line_when_coverage_report_is_requested(): void
    {
        $this->cliArgumentsBuilder
            ->expects($this->once())
            ->method('build')
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

        $initialTestRunCommandLine = $this->adapter->getInitialTestRunCommandLine('--group=default', ['-d', 'memory_limit=-1'], false);

        $this->assertSame(
            [
                '/path/to/phpunit',
                '--group=default',
                '--coverage-xml=/tmp/coverage-xml',
                '--log-junit=/tmp/infection/junit.xml',
            ],
            $initialTestRunCommandLine
        );
    }

    public function outputProvider(): iterable
    {
        yield ['OK, but incomplete, skipped, or risky tests!', true];

        yield ['OK (5 tests, 3 assertions)', true];

        yield ['FAILURES!', false];

        yield ['ERRORS!', false];
    }

    public function memoryReportProvider(): iterable
    {
        yield ['Memory: 8.00MB', 8.0];

        yield ['Memory: 68.00MB', 68.0];

        yield ['Memory: 68.00 MB', 68.0];

        yield ['Time: 2.51 seconds', -1.0];
    }
}
