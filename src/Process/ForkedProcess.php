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

namespace Infection\Process;

use function fclose;
use function feof;
use function fwrite;
use function is_resource;
use function json_encode;
use function microtime;
use Override;
use const PHP_BINARY;
use function preg_match;
use function proc_close;
use function proc_open;
use function register_shutdown_function;
use function sprintf;
use function stream_get_contents;
use function stream_set_blocking;
use function strlen;
use function substr;
use function var_export;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

/**
 * This process runs a PHP script in a fork of a long-lived worker (see resources/fork-worker.php).
 * Each thread has a worker. A worker loads the vendor code a single time, thus a run
 * does not start a new PHP process and does not compile the test framework again.
 *
 * @internal
 */
final class ForkedProcess extends Process
{
    private const string WORKER = __DIR__ . '/../../resources/fork-worker.php';

    private const int WORKER_FAILURE_EXIT_CODE = 255;

    /**
     * @var array<int|string, array{resource, resource, resource}> the process, its input and its output for each thread
     */
    private static array $workers = [];

    /**
     * @var resource|null
     */
    private $workerOutput;

    private string $output = '';

    private ?int $exitCode = null;

    private bool $timedOut = false;

    private bool $timeoutReported = false;

    private float $startTime = 0.0;

    /**
     * @param list<string> $command the PHP script with its arguments
     * @param array<string, string|int> $forkEnv
     */
    public function __construct(
        private readonly array $command,
        private readonly array $forkEnv,
        private readonly float $forkTimeout,
    ) {
        parent::__construct($command, env: $forkEnv, timeout: $forkTimeout);
    }

    /**
     * @param array<string, string|int> $env
     */
    #[Override]
    public function start(?callable $callback = null, array $env = []): void
    {
        [, $input, $this->workerOutput] = self::$workers[$env['TEST_TOKEN'] ?? 0] ??= self::startWorker();

        $this->startTime = microtime(true);

        fwrite($input, json_encode(['argv' => $this->command, 'env' => $env + $this->forkEnv, 'timeout' => $this->forkTimeout]) . "\n");
    }

    /**
     * A run with a timeout stays in the running state until checkTimeout() reports the timeout.
     * Otherwise a result that arrives between checkTimeout() and isRunning() loses its timeout.
     */
    #[Override]
    public function isRunning(): bool
    {
        if ($this->workerOutput === null) {
            return false;
        }

        $this->readResult();

        return $this->exitCode === null || ($this->timedOut && !$this->timeoutReported);
    }

    #[Override]
    public function checkTimeout(): void
    {
        $this->readResult();

        if (!$this->timedOut || $this->timeoutReported) {
            return;
        }

        $this->timeoutReported = true;

        throw new ProcessTimedOutException($this, ProcessTimedOutException::TYPE_GENERAL);
    }

    #[Override]
    public function stop(float $timeout = 10, ?int $signal = null): ?int
    {
        return $this->exitCode;
    }

    #[Override]
    public function isStarted(): bool
    {
        return $this->workerOutput !== null;
    }

    #[Override]
    public function isTerminated(): bool
    {
        return $this->isStarted() && !$this->isRunning();
    }

    #[Override]
    public function getStatus(): string
    {
        if (!$this->isStarted()) {
            return Process::STATUS_READY;
        }

        return $this->isRunning() ? Process::STATUS_STARTED : Process::STATUS_TERMINATED;
    }

    #[Override]
    public function getOutput(): string
    {
        $this->readResult();

        return $this->output;
    }

    #[Override]
    public function getErrorOutput(): string
    {
        return '';
    }

    #[Override]
    public function getExitCode(): ?int
    {
        $this->readResult();

        return $this->exitCode;
    }

    #[Override]
    public function getStartTime(): float
    {
        return $this->startTime;
    }

    /**
     * Reads the available output of the worker, and the result if the run is complete.
     */
    private function readResult(): void
    {
        if ($this->workerOutput === null || $this->exitCode !== null) {
            return;
        }

        $this->output .= stream_get_contents($this->workerOutput);

        if (preg_match('/\0FORK (\d+) (\d)\n$/', $this->output, $matches) === 1) {
            $this->output = substr($this->output, 0, -strlen($matches[0]));
            $this->exitCode = (int) $matches[1];
            $this->timedOut = $matches[2] === '1';

            return;
        }

        if (feof($this->workerOutput)) {
            $this->exitCode = self::WORKER_FAILURE_EXIT_CODE;
            self::forgetWorker($this->workerOutput);
        }
    }

    /**
     * @return array{resource, resource, resource}
     */
    private static function startWorker(): array
    {
        if (self::$workers === []) {
            register_shutdown_function(self::stopWorkers(...));
        }

        // The worker inherits the environment as is, with the PHP configuration of this process.
        $process = proc_open(
            // PHP's CLI does not accept a phar:// path as the script.
            [PHP_BINARY, '-r', sprintf('require %s;', var_export(self::WORKER, true))],
            [['pipe', 'r'], ['pipe', 'w'], ['redirect', 1]],
            $pipes,
        );

        stream_set_blocking($pipes[1], false);

        return [$process, $pipes[0], $pipes[1]];
    }

    private static function stopWorkers(): void
    {
        foreach (self::$workers as [, , $output]) {
            self::forgetWorker($output);
        }
    }

    /**
     * @param resource $workerOutput
     */
    private static function forgetWorker($workerOutput): void
    {
        foreach (self::$workers as $index => [$process, $input, $output]) {
            if ($output !== $workerOutput) {
                continue;
            }

            unset(self::$workers[$index]);

            fclose($input);
            fclose($output);

            if (is_resource($process)) {
                proc_close($process);
            }
        }
    }
}
