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

use Infection\Process\ForkWorker;
use function microtime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Safe\Exceptions\PosixException;
use function Safe\json_decode;
use function Safe\posix_kill;
use function usleep;

#[CoversClass(ForkWorker::class)]
#[Group('integration')]
#[RequiresPhpExtension('pcntl')]
#[RequiresPhpExtension('posix')]
final class ForkWorkerTest extends TestCase
{
    private const string FIXTURES = __DIR__ . '/../Fixtures/ForkWorker';

    private const float WAIT_LIMIT = 10.0;

    private ForkWorker $worker;

    protected function setUp(): void
    {
        $this->worker = ForkWorker::start();
    }

    protected function tearDown(): void
    {
        $this->worker->kill();
    }

    public function test_it_runs_a_script_with_the_arguments_and_the_environment(): void
    {
        $argv = [self::FIXTURES . '/report.php', '--option', 'value'];

        $this->worker->run($argv, ['FOO' => 'bar']);

        $this->assertSame(3, $this->waitForExitCode());
        $this->assertSame('error output', $this->worker->readErrorOutput());

        $report = json_decode($this->worker->readOutput(), true);

        $this->assertSame($argv, $report['argv']);
        $this->assertSame($argv, $report['server_argv']);
        $this->assertSame('bar', $report['foo']);
        $this->assertSame('', $report['stdin'], 'A script must get an end of file on STDIN');
    }

    public static function exitCodeProvider(): iterable
    {
        yield 'output that resembles a result' => ['result_line.php', 4, "0\n"];

        yield 'script stopped by a signal' => ['signal.php', 137, ''];

        yield 'shell script' => ['wrapper.sh', 5, "shell --option\n"];

        yield 'no such file' => ['unknown', 127, ''];
    }

    #[DataProvider('exitCodeProvider')]
    public function test_it_returns_the_exit_code(string $script, int $expectedExitCode, string $expectedOutput): void
    {
        $this->worker->run([self::FIXTURES . '/' . $script, '--option'], []);

        $this->assertSame($expectedExitCode, $this->waitForExitCode());
        $this->assertSame($expectedOutput, $this->worker->readOutput());
    }

    public function test_it_runs_each_script_in_a_new_child_of_the_same_worker(): void
    {
        $this->worker->run([self::FIXTURES . '/report.php'], ['FOO' => 'first']);
        $this->waitForExitCode();
        $first = json_decode($this->worker->readOutput(), true);

        $this->worker->run([self::FIXTURES . '/report.php'], []);
        $this->waitForExitCode();
        $second = json_decode($this->worker->readOutput(), true);

        $this->assertSame($first['worker'], $second['worker']);
        $this->assertFalse($second['foo'], 'The environment of a run must not change the worker');
    }

    public function test_kill_stops_the_script(): void
    {
        $this->worker->run([self::FIXTURES . '/sleep.php'], []);

        $pid = (int) $this->waitFor($this->worker->readOutput(...));

        $this->assertTrue(self::isRunning($pid), 'The script must run before the kill');

        $this->worker->kill();

        $this->assertTrue($this->waitFor(static fn (): bool => !self::isRunning($pid)));
    }

    public function test_kill_stops_the_descendants_of_the_script(): void
    {
        $this->worker->run([self::FIXTURES . '/spawn.php', 'wait'], []);

        $grandchild = (int) $this->waitFor($this->worker->readOutput(...));

        $this->assertTrue(self::isRunning($grandchild), 'The grandchild must run before the kill');

        $this->worker->kill();

        $this->assertTrue($this->waitFor(static fn (): bool => !self::isRunning($grandchild)));
    }

    public function test_it_stops_the_descendants_of_a_complete_script(): void
    {
        $this->worker->run([self::FIXTURES . '/spawn.php'], []);

        $this->assertSame(0, $this->waitForExitCode(), 'A grandchild with the output descriptors must not delay the result');

        $grandchild = (int) $this->worker->readOutput();

        $this->assertTrue($this->waitFor(static fn (): bool => !self::isRunning($grandchild)));
    }

    public function test_it_fails_if_the_worker_stops_without_a_result(): void
    {
        $this->worker->run([self::FIXTURES . '/kill_worker.php'], []);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The fork worker stopped before it returned a result.');

        $this->waitForExitCode();
    }

    private static function isRunning(int $pid): bool
    {
        try {
            posix_kill($pid, 0);
        } catch (PosixException) {
            return false;
        }

        return true;
    }

    private function waitForExitCode(): int
    {
        return $this->waitFor($this->worker->readExitCode(...));
    }

    /**
     * @template T
     *
     * @param callable(): (T|false|''|null) $read
     *
     * @return T
     */
    private function waitFor(callable $read): mixed
    {
        $deadline = microtime(true) + self::WAIT_LIMIT;

        while (microtime(true) < $deadline) {
            $value = $read();

            if ($value !== null && $value !== false && $value !== '') {
                return $value;
            }

            usleep(1000);
        }

        $this->fail('No result before the wait limit.');
    }
}
