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

namespace Infection\Mutation;

use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Process\MutationProcess;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;
use function Safe\sprintf;

/**
 * @internal
 * @final
 */
class MutationExecutionResultFactory
{
    private $testFrameworkAdapter;

    public function __construct(TestFrameworkAdapter $testFrameworkAdapter)
    {
        $this->testFrameworkAdapter = $testFrameworkAdapter;
    }

    public function createFromProcess(MutationProcess $mutationProcess): MutationExecutionResult
    {
        $process = $mutationProcess->getProcess();
        $mutation = $mutationProcess->getMutation();

        return new MutationExecutionResult(
            $process->getCommandLine(),
            $this->retrieveProcessOutput($process),
            $this->retrieveDetectionStatus($mutationProcess),
            $mutation->getDiff(),
            $mutation->getMutatorName(),
            $mutation->getOriginalFilePath(),
            $mutation->getOriginalStartingLine()
        );
    }

    private function retrieveProcessOutput(Process $process): string
    {
        Assert::true(
            $process->isTerminated(),
            sprintf(
                'Cannot retrieve a non-terminated process output. Got "%s"',
                $process->getStatus()
            )
        );

        return $process->getOutput();
    }

    private function retrieveDetectionStatus(MutationProcess $mutationProcess): string
    {
        if (!$mutationProcess->getMutation()->hasTests()) {
            return DetectionStatus::NOT_COVERED;
        }

        if ($mutationProcess->isTimedOut()) {
            return DetectionStatus::TIMED_OUT;
        }

        $process = $mutationProcess->getProcess();

        if ($process->getExitCode() > 100) {
            // See \Symfony\Component\Process\Process::$exitCodes
            return DetectionStatus::ERROR;
        }

        if ($this->testFrameworkAdapter->testsPass($this->retrieveProcessOutput($process))) {
            return DetectionStatus::ESCAPED;
        }

        return DetectionStatus::KILLED;
    }
}
