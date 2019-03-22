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

use Infection\Mutant\MutantInterface;
use Infection\MutationInterface;
use Infection\Mutator\Util\Mutator;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
final class MutantProcess implements MutantProcessInterface
{
    public const CODE_KILLED = 0;
    public const CODE_ESCAPED = 1;
    public const CODE_ERROR = 2;
    public const CODE_TIMED_OUT = 3;
    public const CODE_NOT_COVERED = 4;

    private const PROCESS_OK = 0;
    private const PROCESS_GENERAL_ERROR = 1;
    private const PROCESS_MISUSE_SHELL_BUILTINS = 2;

    private const NOT_FATAL_ERROR_CODES = [
        self::PROCESS_OK,
        self::PROCESS_GENERAL_ERROR,
        self::PROCESS_MISUSE_SHELL_BUILTINS,
    ];

    /**
     * @var Process
     */
    private $process;

    /**
     * @var MutantInterface
     */
    private $mutant;

    /**
     * @var bool
     */
    private $isTimedOut = false;

    /**
     * @var AbstractTestFrameworkAdapter
     */
    private $testFrameworkAdapter;

    public function __construct(Process $process, MutantInterface $mutant, AbstractTestFrameworkAdapter $testFrameworkAdapter)
    {
        $this->process = $process;
        $this->mutant = $mutant;
        $this->testFrameworkAdapter = $testFrameworkAdapter;
    }

    public function getProcess(): Process
    {
        return $this->process;
    }

    public function getMutant(): MutantInterface
    {
        return $this->mutant;
    }

    public function markTimeout(): void
    {
        $this->isTimedOut = true;
    }

    public function getResultCode(): int
    {
        if (!$this->getMutant()->isCoveredByTest()) {
            return self::CODE_NOT_COVERED;
        }

        if ($this->isTimedOut()) {
            return self::CODE_TIMED_OUT;
        }

        if (!\in_array($this->getProcess()->getExitCode(), self::NOT_FATAL_ERROR_CODES, true)) {
            return self::CODE_ERROR;
        }

        if ($this->testFrameworkAdapter->testsPass($this->getProcess()->getOutput())) {
            return self::CODE_ESCAPED;
        }

        return self::CODE_KILLED;
    }

    public function getMutator(): Mutator
    {
        return $this->getMutation()->getMutator();
    }

    public function getOriginalFilePath(): string
    {
        return $this->getMutation()->getOriginalFilePath();
    }

    public function getOriginalStartingLine(): int
    {
        return (int) $this->getMutation()->getAttributes()['startLine'];
    }

    private function isTimedOut(): bool
    {
        return $this->isTimedOut;
    }

    private function getMutation(): MutationInterface
    {
        return $this->getMutant()->getMutation();
    }
}
