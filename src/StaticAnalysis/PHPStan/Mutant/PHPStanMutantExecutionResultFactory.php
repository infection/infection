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

namespace Infection\StaticAnalysis\PHPStan\Mutant;

use Infection\Mutant\DetectionStatus;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutant\MutantExecutionResultFactory;
use Infection\Process\MutantProcess;
use function sprintf;
use Symfony\Component\Process\Process;
use function trim;
use Webmozart\Assert\Assert;

/**
 * @internal
 * @final
 */
class PHPStanMutantExecutionResultFactory implements MutantExecutionResultFactory
{
    private const PROCESS_MIN_ERROR_CODE = 100;

    public function createFromProcess(MutantProcess $mutantProcess): MutantExecutionResult
    {
        $process = $mutantProcess->getProcess();
        $mutant = $mutantProcess->getMutant();
        $mutation = $mutant->getMutation();

        return new MutantExecutionResult(
            $process->getCommandLine(),
            $this->retrieveProcessOutput($process),
            $this->retrieveDetectionStatus($mutantProcess),
            $mutant->getDiff(),
            $mutation->getHash(),
            $mutation->getMutatorClass(),
            $mutation->getMutatorName(),
            $mutation->getOriginalFilePath(),
            $mutation->getOriginalStartingLine(),
            $mutation->getOriginalEndingLine(),
            $mutation->getOriginalStartFilePosition(),
            $mutation->getOriginalEndFilePosition(),
            $mutant->getPrettyPrintedOriginalCode(),
            $mutant->getMutatedCode(),
            $mutant->getTests(),
            $mutantProcess->getFinishedAt() - $process->getStartTime(),
        );
    }

    private function retrieveProcessOutput(Process $process): string
    {
        Assert::true(
            $process->isTerminated(),
            sprintf(
                'Cannot retrieve a non-terminated process output. Got "%s"',
                $process->getStatus(),
            ),
        );

        return trim($process->getOutput() . "\n\n" . $process->getErrorOutput());
    }

    private function retrieveDetectionStatus(MutantProcess $mutantProcess): string
    {
        if ($mutantProcess->isTimedOut()) {
            return DetectionStatus::TIMED_OUT;
        }

        $process = $mutantProcess->getProcess();

        if ($process->getExitCode() > self::PROCESS_MIN_ERROR_CODE) {
            // See \Symfony\Component\Process\Process::$exitCodes
            return DetectionStatus::ERROR;
        }

        if ($process->getExitCode() === 0) {
            return DetectionStatus::ESCAPED;
        }

        return DetectionStatus::KILLED_BY_STATIC_ANALYSIS;
    }
}
