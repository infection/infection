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

use Infection\Mutant\DetectionStatus;
use Infection\Mutant\Mutant;
use Infection\TestFramework\Common\Process\DryRunProcess;
use Infection\TestFramework\Contracts\Mutant\MutantProcessDetectionStatusResolver\MutantProcessDetectionStatusResolver;
use Infection\TestFramework\Contracts\Mutant\MutantProcessDetectionStatusResolver\SuspiciousResult;
use function microtime;
use Symfony\Component\Process\Process;
use function trim;
use Webmozart\Assert\Assert;

/**
 * @internal
 * @final
 */
class MutantProcess
{
    private bool $timedOut = false;

    private float $finishedAt = 0.0;

    private DetectionStatus|SuspiciousResult|null $result = null;

    public function __construct(
        private Process $process,
        private readonly Mutant $mutant,
        private readonly MutantProcessDetectionStatusResolver $resolver,
        private readonly string $testFrameworkName,
        private readonly bool $includeErrorOutput = false,
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
        $this->finishedAt = microtime(true);
    }

    public function getFinishedAt(): float
    {
        return $this->finishedAt;
    }

    public function getStopSignal(): ?int
    {
        if ($this->process instanceof DryRunProcess || !$this->process->hasBeenStopped()) {
            return null;
        }

        return $this->process->getStopSignal();
    }

    public function getRuntime(): float
    {
        return $this->process instanceof DryRunProcess
            ? 0.0
            : $this->finishedAt - $this->process->getStartTime();
    }

    public function simulate(): void
    {
        Assert::true(
            $this->process instanceof DryRunProcess || !$this->process->isStarted(),
            'Cannot simulate a process after it has started.',
        );
        Assert::same($this->finishedAt, 0.0, 'Cannot simulate an already completed evaluation.');

        if (!$this->process instanceof DryRunProcess) {
            $this->process = DryRunProcess::fromProcess($this->process);
        }

        $this->markAsFinished();
    }

    public function getResult(): DetectionStatus|SuspiciousResult
    {
        Assert::greaterThan($this->finishedAt, 0.0, 'Cannot classify an evaluation before its process has finished.');

        return $this->result ??= $this->resolveResult();
    }

    public function getResultOutput(): string
    {
        $output = $this->process->getOutput();

        return $this->includeErrorOutput
            ? trim($output . "\n\n" . $this->process->getErrorOutput())
            : $output;
    }

    private function resolveResult(): DetectionStatus|SuspiciousResult
    {
        $exitCode = $this->process->getExitCode();
        Assert::notNull($exitCode, 'A completed process must have an exit code.');

        return $this->resolver->tryToResolve(
            $this->process->getOutput(),
            $this->process->getErrorOutput(),
            $exitCode,
            $this->timedOut,
            $this->getStopSignal(),
        );
    }
}
