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

use function array_filter;
use DuoClock\DuoClock;
use function function_exists;
use function getenv;
use function is_file;
use Override;
use function register_shutdown_function;
use function Safe\file_get_contents;
use function Safe\preg_match;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

/**
 * This process runs a PHP script in a fork of a long-lived worker: a run does not start a new PHP process.
 * Each thread has a worker. This class measures the timeout, and stops the worker at the timeout.
 *
 * @internal
 */
final class ForkedProcess extends Process
{
    private const int KILLED_EXIT_CODE = 137;

    private const float STOP_TIMEOUT = 10.0;

    private const int FIRST_LINE_LENGTH = 80;

    /**
     * @var array<int|string, ForkWorker> the worker of each thread
     */
    private static array $workers = [];

    private ?ForkWorker $worker = null;

    private string $output = '';

    private string $errorOutput = '';

    private ?int $exitCode = null;

    private float $startTime = 0.0;

    /**
     * @param array<string> $command the PHP script with its arguments
     * @param array<string, string|int> $forkEnv
     */
    public function __construct(
        private readonly array $command,
        private readonly array $forkEnv,
        private readonly float $forkTimeout,
        private readonly DuoClock $clock = new DuoClock(),
    ) {
        parent::__construct($command, env: $forkEnv, timeout: $forkTimeout);
    }

    /**
     * The worker requires the first element of the command: it must be a PHP script. To disable the fork: INFECTION_FORK=0.
     *
     * @param array<string> $command
     */
    public static function supports(array $command): bool
    {
        return getenv('INFECTION_FORK') !== '0'
            && function_exists('pcntl_fork')
            && function_exists('posix_kill')
            && is_file($command[0])
            && preg_match('/^(#!.*\bphp\b|<\?php)/', file_get_contents($command[0], length: self::FIRST_LINE_LENGTH)) === 1;
    }

    /**
     * @param array<mixed> $env
     */
    #[Override]
    public function start(?callable $callback = null, array $env = []): void
    {
        if (self::$workers === []) {
            register_shutdown_function(self::killWorkers(...));
        }

        $this->startTime = $this->clock->microtime();

        $this->worker = self::$workers[$env['TEST_TOKEN'] ?? 0] ??= ForkWorker::start();
        $this->worker->run($this->command, $env + $this->forkEnv);
    }

    #[Override]
    public function isRunning(): bool
    {
        if ($this->worker === null || $this->exitCode !== null) {
            return false;
        }

        // The exit code first: after it, the output is complete.
        $this->exitCode = $this->worker->readExitCode();
        $this->output .= $this->worker->readOutput();
        $this->errorOutput .= $this->worker->readErrorOutput();

        return $this->exitCode === null;
    }

    #[Override]
    public function checkTimeout(): void
    {
        if (!$this->isRunning() || $this->clock->microtime() - $this->startTime < $this->forkTimeout) {
            return;
        }

        $this->stop();

        throw new ProcessTimedOutException($this, ProcessTimedOutException::TYPE_GENERAL);
    }

    /**
     * Stops the script together with the worker. The next run of the thread starts a new worker.
     */
    #[Override]
    public function stop(float $timeout = self::STOP_TIMEOUT, ?int $signal = null): ?int
    {
        if ($this->worker !== null && $this->isRunning()) {
            $this->worker->kill();
            self::$workers = array_filter(self::$workers, fn (ForkWorker $worker): bool => $worker !== $this->worker);

            $this->exitCode = self::KILLED_EXIT_CODE;
        }

        return $this->exitCode;
    }

    #[Override]
    public function isStarted(): bool
    {
        return $this->worker !== null;
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
        $this->isRunning();

        return $this->output;
    }

    #[Override]
    public function getErrorOutput(): string
    {
        $this->isRunning();

        return $this->errorOutput;
    }

    #[Override]
    public function getExitCode(): ?int
    {
        $this->isRunning();

        return $this->exitCode;
    }

    #[Override]
    public function getStartTime(): float
    {
        return $this->startTime;
    }

    private static function killWorkers(): void
    {
        foreach (self::$workers as $worker) {
            $worker->kill();
        }

        self::$workers = [];
    }
}
