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

namespace Infection\Tests\Process;

use DuoClock\TimeSpy;
use Infection\Process\ForkedProcess;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\WithEnvironmentVariable;
use PHPUnit\Framework\TestCase;
use function Safe\json_decode;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;
use function usleep;

#[CoversClass(ForkedProcess::class)]
#[Group('integration')]
#[RequiresPhpExtension('pcntl')]
#[RequiresPhpExtension('posix')]
final class ForkedProcessTest extends TestCase
{
    private const string FIXTURES = __DIR__ . '/../Fixtures/ForkWorker';

    public static function commandProvider(): iterable
    {
        yield 'PHP script' => [self::FIXTURES . '/report.php', true];

        yield 'PHP script with an interpreter line' => [__DIR__ . '/../../../bin/infection', true];

        yield 'shell script' => [self::FIXTURES . '/wrapper.sh', false];

        yield 'no such file' => [self::FIXTURES . '/unknown', false];
    }

    #[DataProvider('commandProvider')]
    public function test_it_supports_php_scripts_only(string $script, bool $expected): void
    {
        $this->assertSame($expected, ForkedProcess::supports([$script, '--option']));
    }

    #[WithEnvironmentVariable('INFECTION_FORK', '0')]
    public function test_it_does_not_support_a_command_if_disabled(): void
    {
        $this->assertFalse(ForkedProcess::supports([self::FIXTURES . '/report.php']));
    }

    public function test_it_runs_a_script_in_a_fork_of_the_worker(): void
    {
        $process = new ForkedProcess([self::FIXTURES . '/report.php'], ['FOO' => 'bar'], 10.0, new TimeSpy(100.0));

        $this->assertFalse($process->isStarted());
        $this->assertSame(Process::STATUS_READY, $process->getStatus());
        $this->assertFalse($process->isRunning());

        $process->start();

        $this->assertTrue($process->isStarted());

        self::wait($process);

        $this->assertTrue($process->isTerminated());
        $this->assertSame(Process::STATUS_TERMINATED, $process->getStatus());
        $this->assertSame('bar', json_decode($process->getOutput(), true)['foo']);
        $this->assertSame('error output', $process->getErrorOutput());
        $this->assertSame(3, $process->getExitCode());
        $this->assertSame(100.0, $process->getStartTime());
    }

    public function test_a_thread_keeps_its_worker(): void
    {
        $this->assertSame(self::runReport('1')['worker'], self::runReport('1')['worker']);
        $this->assertNotSame(self::runReport('1')['worker'], self::runReport('2')['worker']);
    }

    public function test_it_stops_the_script_at_the_timeout(): void
    {
        $clock = new TimeSpy(100.0);
        $process = new ForkedProcess([self::FIXTURES . '/sleep.php'], [], 0.2, $clock);
        $process->start(env: ['TEST_TOKEN' => 'timeout']);

        $clock->usleep(199_999);
        $process->checkTimeout();

        $this->assertTrue($process->isRunning(), 'The script must run before the deadline');

        $clock->usleep(1);

        try {
            $process->checkTimeout();

            $this->fail('The process must report the timeout.');
        } catch (ProcessTimedOutException $exception) {
            $this->assertSame($process, $exception->getProcess());
        }

        $this->assertFalse($process->isRunning());
        $this->assertSame(137, $process->getExitCode());

        $process->checkTimeout();

        $this->assertIsInt(self::runReport('timeout')['worker'], 'The thread must run the next script');
    }

    public function test_stop_stops_the_script(): void
    {
        $process = new ForkedProcess([self::FIXTURES . '/sleep.php'], [], 10.0);
        $process->start();

        $this->assertSame(Process::STATUS_STARTED, $process->getStatus());
        $this->assertSame(137, $process->stop());
        $this->assertFalse($process->isRunning());
    }

    public function test_stop_keeps_the_exit_code_of_a_complete_run(): void
    {
        $process = new ForkedProcess([self::FIXTURES . '/report.php'], [], 10.0);
        $process->start();

        self::wait($process);

        $this->assertSame(3, $process->stop());
    }

    /**
     * @return array{worker: int}
     */
    private static function runReport(string $thread): array
    {
        $process = new ForkedProcess([self::FIXTURES . '/report.php'], [], 10.0);
        $process->start(env: ['TEST_TOKEN' => $thread]);

        self::wait($process);

        return json_decode($process->getOutput(), true);
    }

    private static function wait(ForkedProcess $process): void
    {
        while ($process->isRunning()) {
            $process->checkTimeout();

            usleep(1000);
        }
    }
}
