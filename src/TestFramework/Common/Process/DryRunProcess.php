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

namespace Infection\TestFramework\Common\Process;

use Override;
use Symfony\Component\Process\Process;

/**
 * @internal
 *
 * Represents a terminated process without running its command.
 * By default it exposes passing test output and a zero exit code.
 */
final class DryRunProcess extends Process
{
    /**
     * Output that TestFrameworkAdapter::testsPass() recognizes as passing tests.
     * The pattern "OK (" triggers testsPass() to return true, causing ESCAPED status.
     */
    public const string PASSING_TEST_OUTPUT = 'OK (0 tests, 0 assertions)';

    private string $output = self::PASSING_TEST_OUTPUT;

    private string $errorOutput = '';

    private float $startTime = 0.0;

    private ?int $exitCode = 0;

    // TODO: extract this parameterization in a dedicated PR.
    public static function fromProcess(
        Process $process,
        string $output = self::PASSING_TEST_OUTPUT,
        string $errorOutput = '',
        float $startTime = 0.0,
        ?int $exitCode = 0,
    ): self {
        $dryRunProcess = self::fromShellCommandline($process->getCommandLine());
        $dryRunProcess->output = $output;
        $dryRunProcess->errorOutput = $errorOutput;
        $dryRunProcess->startTime = $startTime;
        $dryRunProcess->exitCode = $exitCode;

        return $dryRunProcess;
    }

    #[Override]
    public function isTerminated(): bool
    {
        return true;
    }

    #[Override]
    public function isStarted(): bool
    {
        return true;
    }

    #[Override]
    public function getOutput(): string
    {
        return $this->output;
    }

    #[Override]
    public function getErrorOutput(): string
    {
        return $this->errorOutput;
    }

    #[Override]
    public function getStartTime(): float
    {
        return $this->startTime;
    }

    #[Override]
    public function getExitCode(): ?int
    {
        return $this->exitCode;
    }

    #[Override]
    public function getStatus(): string
    {
        return Process::STATUS_TERMINATED;
    }
}
