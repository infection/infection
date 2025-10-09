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

use Symfony\Component\Process\Process;

/**
 * @internal
 *
 * Wraps a real Process to simulate a terminated process for dry-run mode.
 *
 * The real process is constructed normally (ensuring command-line construction
 * is exercised) but never started. This wrapper presents it as already terminated
 * with passing test output, causing all mutants to be marked as ESCAPED.
 */
final class DryRunProcess extends Process
{
    private readonly string $commandLine;

    public function __construct(Process $realProcess)
    {
        // Create a minimal Process with dummy command
        parent::__construct(['true']);

        // Store the real command line from the actual process
        $this->commandLine = $realProcess->getCommandLine();
    }

    public function getCommandLine(): string
    {
        return $this->commandLine;
    }

    public function isTerminated(): bool
    {
        return true;
    }

    public function isStarted(): bool
    {
        return true;
    }

    public function getOutput(): string
    {
        // Returns output that TestFrameworkAdapter::testsPass() recognizes as passing tests.
        // The pattern "OK (" triggers testsPass() to return true. Combined with exit code 0,
        // this causes mutants to be marked as ESCAPED, which is correct for dry-run mode where
        // tests are not actually executed.
        return 'OK (0 tests, 0 assertions)';
    }

    public function getStartTime(): float
    {
        return 0.0;
    }

    public function getExitCode(): ?int
    {
        return 0;
    }

    public function getStatus(): string
    {
        return Process::STATUS_TERMINATED;
    }
}
