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

use function in_array;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\Mutant;
use Infection\Process\Runner\Parallel\ProcessBearer;
use function Safe\sprintf;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;

/**
 * @internal
 * @final
 */
class MutantProcess implements ProcessBearer
{
    private const PROCESS_OK = 0;
    private const PROCESS_GENERAL_ERROR = 1;
    private const PROCESS_MISUSE_SHELL_BUILTINS = 2;

    private const NOT_FATAL_ERROR_CODES = [
        self::PROCESS_OK,
        self::PROCESS_GENERAL_ERROR,
        self::PROCESS_MISUSE_SHELL_BUILTINS,
    ];

    private $process;
    private $mutant;

    /**
     * @var bool
     */
    private $timeout = false;
    private $testFrameworkAdapter;

    public function __construct(
        Process $process,
        Mutant $mutant,
        TestFrameworkAdapter $testFrameworkAdapter
    ) {
        $this->process = $process;
        $this->mutant = $mutant;
        $this->testFrameworkAdapter = $testFrameworkAdapter;
    }

    public function getProcess(): Process
    {
        return $this->process;
    }

    public function getMutant(): Mutant
    {
        return $this->mutant;
    }

    public function markAsTimedOut(): void
    {
        $this->timeout = true;
    }

    public function retrieveProcessOutput(): string
    {
        Assert::true(
            $this->process->isTerminated(),
            sprintf(
                'Cannot retrieve a non-terminated process output. Got "%s"',
                $this->process->getStatus()
            )
        );

        return $this->process->getOutput();
    }

    public function retrieveDetectionStatus(): string
    {
        if (!$this->mutant->isCoveredByTest()) {
            return DetectionStatus::NOT_COVERED;
        }

        if ($this->timeout) {
            return DetectionStatus::TIMED_OUT;
        }

        if (!in_array($this->getProcess()->getExitCode(), self::NOT_FATAL_ERROR_CODES, true)) {
            return DetectionStatus::ERROR;
        }

        if ($this->testFrameworkAdapter->testsPass($this->retrieveProcessOutput())) {
            return DetectionStatus::ESCAPED;
        }

        return DetectionStatus::KILLED;
    }
}
