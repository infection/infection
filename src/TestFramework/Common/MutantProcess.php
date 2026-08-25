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

namespace Infection\TestFramework\Common;

use DuoClock\DuoClock;
use Infection\TestFramework\Contracts\MutantProcess as MutantProcessContract;
use Infection\TestFramework\Contracts\MutantProcessResult;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class MutantProcess implements MutantProcessContract
{
    private bool $timedOut = false;

    private ?float $finishedAt = null;

    public function __construct(
        private readonly Process $process,
        private readonly MutantProcessDetectionStatusResolver $detectionStatusResolver,
        private readonly DuoClock $clock,
    ) {
    }

    public function getProcess(): Process
    {
        return $this->process;
    }

    public function markAsTimedOut(): void
    {
        $this->timedOut = true;
    }

    public function markAsFinished(): void
    {
        $this->finishedAt = $this->clock->microtime();
    }

    public function getResult(): MutantProcessResult
    {
        $finishedAt = $this->finishedAt;
        Assert::notNull($finishedAt, 'Should have been started.');
        $exitCode = $this->process->getExitCode();
        Assert::integer($exitCode, 'A finished mutant process must have an exit code.');

        return new MutantProcessResult(
            commandLine: $this->process->getCommandLine(),
            stdout: $this->process->getOutput(),
            stderr: $this->process->getErrorOutput(),
            startedAt: $this->process->getStartTime(),
            finishedAt: $finishedAt,
            detectionStatus: $this->detectionStatusResolver->resolve(
                $this->process->getOutput(),
                $this->process->getErrorOutput(),
                $exitCode,
                $this->timedOut,
            ),
        );
    }
}
