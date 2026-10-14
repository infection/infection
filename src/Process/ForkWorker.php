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

use function feof;
use function fgets;
use const PHP_BINARY;
use function proc_close;
use function proc_open;
use function proc_terminate;
use function Safe\fwrite;
use function Safe\json_encode;
use function Safe\stream_get_contents;
use function Safe\stream_set_blocking;
use function sprintf;
use function var_export;
use Webmozart\Assert\Assert;

/**
 * This class starts a fork worker (see resources/fork-worker.php) and exchanges requests and results with it.
 * The worker takes a request on descriptor 3 and returns the exit code of the script on descriptor 4. Its
 * own STDIN is /dev/null, so a script that reads STDIN gets an end of file. A worker runs a single script at
 * a time, in a fork of its own.
 *
 * @internal
 */
final class ForkWorker
{
    private const string SCRIPT = __DIR__ . '/../../resources/fork-worker.php';

    private const int REQUESTS = 3;

    /**
     * The exit code of a run whose worker died before it returned a result. It is above
     * TestFrameworkMutantExecutionResultFactory::PROCESS_MIN_ERROR_CODE, so the run is an error and
     * not a mutation killed by the tests.
     */
    private const int LOST_EXIT_CODE = 143;

    private const int RESULTS = 4;

    /**
     * Whether the worker died and cannot run another script.
     */
    private bool $lost = false;

    /**
     * @param resource|null $process
     * @param array<int, resource> $pipes
     */
    private function __construct(
        private $process,
        private readonly array $pipes,
    ) {
    }

    public static function start(): self
    {
        $pipes = [];

        // The worker inherits the environment as is, with the PHP configuration of this process.
        // @phpstan-ignore theCodingMachineSafe.function (Safe\proc_open() does not accept a list of arguments.)
        $process = proc_open(
            // PHP's CLI does not accept a phar:// path as the script.
            [PHP_BINARY, '-r', sprintf('require %s;', var_export(self::SCRIPT, true))],
            // A script that reads STDIN gets an end of file.
            [['file', '/dev/null', 'r'], ['pipe', 'w'], ['pipe', 'w'], self::REQUESTS => ['pipe', 'r'], self::RESULTS => ['pipe', 'w']],
            $pipes,
        );

        Assert::resource($process, message: 'Could not start the fork worker.');

        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);
        stream_set_blocking($pipes[self::RESULTS], false);

        return new self($process, $pipes);
    }

    /**
     * @param array<string> $argv the PHP script with its arguments
     * @param array<string, string|int> $env
     */
    public function run(array $argv, array $env): void
    {
        fwrite($this->pipes[self::REQUESTS], json_encode(['argv' => $argv, 'env' => $env]) . "\n");
    }

    /**
     * Returns null while the script runs. Read the exit code before the last read of the output.
     *
     * An end of file without a result means the worker died. It is reported as a stopped script
     * rather than as an exception: the exception would stop the whole analysis on the way through
     * the poll loop of the runner, while the runner already knows how to report a stopped process.
     */
    public function readExitCode(): ?int
    {
        if ($this->hasClosedPipes()) {
            return self::LOST_EXIT_CODE;
        }

        $line = fgets($this->pipes[self::RESULTS]);

        if ($line !== false) {
            return (int) $line;
        }

        if (!feof($this->pipes[self::RESULTS])) {
            return null;
        }

        $this->lost = true;

        return self::LOST_EXIT_CODE;
    }

    /**
     * Whether the worker died without returning a result, and must not run another script. A script
     * stopped by SIGTERM reports the same exit code, so the death is tracked apart from the code.
     */
    public function isLost(): bool
    {
        return $this->lost;
    }

    public function readOutput(): string
    {
        return $this->hasClosedPipes() ? '' : stream_get_contents($this->pipes[1]);
    }

    public function readErrorOutput(): string
    {
        return $this->hasClosedPipes() ? '' : stream_get_contents($this->pipes[2]);
    }

    /**
     * Stops the worker together with its current script.
     */
    public function kill(): void
    {
        $process = $this->process;

        if ($process === null) {
            return;
        }

        $this->lost = true;

        proc_terminate($process);
        // @phpstan-ignore theCodingMachineSafe.function (Safe\proc_close() rejects the status of a worker that a signal stopped.)
        proc_close($process);

        $this->process = null;
    }

    /**
     * Whether kill() closed the pipes of the worker together with the worker itself. A worker that died
     * on its own keeps its pipes open, so the output it wrote stays readable, and that output is often
     * the only explanation of its death.
     */
    private function hasClosedPipes(): bool
    {
        return $this->process === null;
    }
}
