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
use RuntimeException;
use function Safe\fwrite;
use function Safe\json_encode;
use function Safe\stream_get_contents;
use function Safe\stream_set_blocking;
use function sprintf;
use function var_export;
use Webmozart\Assert\Assert;

/**
 * This class starts a fork worker (see resources/fork-worker.php) and exchanges requests and results with it.
 * A worker runs a single script at a time.
 *
 * @internal
 */
final class ForkWorker
{
    private const string SCRIPT = __DIR__ . '/../../resources/fork-worker.php';

    private const int REQUESTS = 3;

    private const int RESULTS = 4;

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
     */
    public function readExitCode(): ?int
    {
        $line = fgets($this->pipes[self::RESULTS]);

        if ($line !== false) {
            return (int) $line;
        }

        if (feof($this->pipes[self::RESULTS])) {
            throw new RuntimeException('The fork worker stopped before it returned a result.');
        }

        return null;
    }

    public function readOutput(): string
    {
        return stream_get_contents($this->pipes[1]);
    }

    public function readErrorOutput(): string
    {
        return stream_get_contents($this->pipes[2]);
    }

    /**
     * Stops the worker together with its current script.
     */
    public function kill(): void
    {
        if ($this->process === null) {
            return;
        }

        proc_terminate($this->process);
        // @phpstan-ignore theCodingMachineSafe.function (Safe\proc_close() rejects the status of a worker that a signal stopped.)
        proc_close($this->process);

        $this->process = null;
    }
}
