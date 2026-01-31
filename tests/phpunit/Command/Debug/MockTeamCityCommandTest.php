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

namespace Infection\Tests\Command\Debug;

use Closure;
use function explode;
use Infection\Command\Debug\MockTeamCityCommand;
use Infection\Configuration\Schema\SchemaConfiguration;
use Infection\Console\Application;
use Infection\Container\Container;
use Infection\Tests\Configuration\Schema\SchemaConfigurationBuilder;
use Infection\Tests\FileSystem\FileSystemTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use function Safe\file_put_contents;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

#[Group('integration')]
#[CoversClass(MockTeamCityCommand::class)]
final class MockTeamCityCommandTest extends FileSystemTestCase
{
    private const LOG_EXAMPLE = <<<'TEAMCITY'
        ##teamcity[testSuiteStarted name='MySuite']
        ##teamcity[testStarted name='testExample']
        ##teamcity[testFinished name='testExample' duration='100']
        ##teamcity[testSuiteFinished name='MySuite']
        TEAMCITY;

    public function test_it_outputs_log_lines_from_file(): void
    {
        $teamCityLog = $this->createTeamCityLog();

        $recordedSleepCalls = [];

        $tester = $this->createCommandTester(
            self::createSleepMock($recordedSleepCalls),
        );

        $tester->execute([
            'log' => $teamCityLog,
            '--time' => '100',
        ]);

        $tester->assertCommandIsSuccessful();

        $this->assertSame(self::LOG_EXAMPLE . "\n", $tester->getDisplay());
        $this->assertSame([100_000, 100_000, 100_000, 100_000], $recordedSleepCalls);
    }

    public function test_it_reads_log_from_stdin_when_no_file_provided(): void
    {
        $recordedSleepCalls = [];

        $tester = $this->createCommandTester(
            self::createSleepMock($recordedSleepCalls),
        );
        $tester->setInputs(explode("\n", self::LOG_EXAMPLE));

        $tester->execute(['--time' => '50']);

        $tester->assertCommandIsSuccessful();

        $this->assertSame(self::LOG_EXAMPLE . "\n\n", $tester->getDisplay());
        $this->assertSame([50_000, 50_000, 50_000, 50_000, 50_000], $recordedSleepCalls);
    }

    public function test_it_uses_default_time_when_not_specified(): void
    {
        $teamCityLog = $this->createTeamCityLog();

        $sleepCalls = [];
        $mockSleep = static function (int $microseconds) use (&$sleepCalls): void {
            $sleepCalls[] = $microseconds;
        };

        $tester = $this->createCommandTester($mockSleep);

        $tester->execute([
            'log' => $teamCityLog,
        ]);

        $tester->assertCommandIsSuccessful();
        $this->assertSame([500_000, 500_000, 500_000, 500_000], $sleepCalls); // Default is 500ms = 500,000 microseconds
    }

    public function test_it_handles_empty_log_file(): void
    {
        $teamCityLog = $this->createTeamCityLog('');

        $recordedSleepCalls = [];

        $tester = $this->createCommandTester(
            self::createSleepMock($recordedSleepCalls),
        );

        $tester->execute([
            'log' => $teamCityLog,
            '--time' => '100',
        ]);

        $tester->assertCommandIsSuccessful();
        $this->assertSame("\n", $tester->getDisplay());
        $this->assertCount(1, $recordedSleepCalls); // One sleep for the empty line
    }

    #[DataProvider('invalidTimeValueProvider')]
    public function test_it_rejects_invalid_time_values(string $timeValue, string $expectedMessage): void
    {
        $teamCityLog = $this->createTeamCityLog();

        $tester = $this->createCommandTester();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $tester->execute([
            'log' => $teamCityLog,
            '--time' => $timeValue,
        ]);
    }

    public static function invalidTimeValueProvider(): iterable
    {
        yield 'no value' => [
            'timeValue' => '',
            'expectedMessage' => 'Expected a natural value for the option "--time". Got "".',
        ];

        yield 'non-numeric string' => [
            'timeValue' => 'abc',
            'expectedMessage' => 'Expected a natural value for the option "--time". Got "abc".',
        ];

        yield 'negative value' => [
            'timeValue' => '-100',
            'expectedMessage' => 'Expected a natural value for the option "--time". Got "-100".',
        ];

        yield 'float value' => [
            'timeValue' => '10.5',
            'expectedMessage' => 'Expected a natural value for the option "--time". Got "10.5".',
        ];
    }

    /**
     * @param (Closure(positive-int|0):void)|null $sleep
     */
    private function createCommandTester(?Closure $sleep = null): CommandTester
    {
        $container = Container::create()
            ->cloneWithService(
                SchemaConfiguration::class,
                SchemaConfigurationBuilder::withMinimalTestData()->build(),
            );

        $application = new Application($container);

        $command = new MockTeamCityCommand(
            new Filesystem(),
            $sleep,
        );
        $command->setApplication($application);

        return new CommandTester($command);
    }

    /**
     * @param list<int> $recordedCalls
     *
     * @return Closure(positive-int|0):void
     */
    private static function createSleepMock(array &$recordedCalls): Closure
    {
        return static function (int $microseconds) use (&$recordedCalls): void {
            $recordedCalls[] = $microseconds;
        };
    }

    private function createTeamCityLog(string $content = self::LOG_EXAMPLE): string
    {
        $teamCityLog = $this->tmp . '/teamcity.log';
        file_put_contents($teamCityLog, $content);

        return $teamCityLog;
    }
}
