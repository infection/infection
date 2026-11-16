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

use DuoClock\DuoClock;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\Mutant;
use Infection\TestFramework\Contracts\Mutant\MutantProcessDetectionStatusResolver;
use function sprintf;
use Symfony\Component\Process\Process;
use function trim;
use Webmozart\Assert\Assert;

/**
 * @internal
 * @final
 */
class MutantProcess
{
    private ?DetectionStatus $result = null;

    private bool $timedOut = false;

    private float $finishedAt = 0.0;

    public function __construct(
        private readonly Process $process,
        private readonly Mutant $mutant,
        private readonly string $testFrameworkName,
        private readonly MutantProcessDetectionStatusResolver $detectionStatusResolver,
        private readonly bool $includeErrorOutput,
        private readonly DuoClock $clock = new DuoClock(),
    ) {
    }

    public function getProcess(): Process
    {
        return $this->process;
    }

    public function getMutant(): Mutant
    {
        return $this->mutant;
    }

    public function getTestFrameworkName(): string
    {
        return $this->testFrameworkName;
    }

    public function markAsTimedOut(): void
    {
        $this->timedOut = true;
    }

    public function isTimedOut(): bool
    {
        return $this->timedOut;
    }

    public function markAsFinished(): void
    {
        $this->finishedAt = $this->clock->microtime();
    }

    public function getFinishedAt(): float
    {
        return $this->finishedAt;
    }

    public function getRuntime(): float
    {
        return $this->process instanceof DryRunProcess
            ? 0.0
            : $this->finishedAt - $this->process->getStartTime();
    }

    public function getResult(): DetectionStatus
    {
        Assert::greaterThan(
            $this->finishedAt,
            0.0,
            'Cannot resolve the detection status before the mutant process has finished.',
        );

        return $this->result ??= $this->detectionStatusResolver->resolve(
            $this->process->getOutput(),
            $this->process->getExitCode(),
            $this->timedOut,
        );
    }

    public function getResultOutput(): string
    {
        $output = $this->process->getOutput();

        return $this->includeErrorOutput
            ? trim(
                sprintf(
                    "%s\n\n%s",
                    $output,
                    $this->process->getErrorOutput(),
                ),
            )
            : $output;
    }
}
