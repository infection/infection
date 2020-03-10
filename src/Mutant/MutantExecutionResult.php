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

namespace Infection\Mutant;

use function array_keys;
use Infection\Mutator\ProfileList;
use Infection\Process\MutantProcess;
use Webmozart\Assert\Assert;

/**
 * @internal
 * @final
 */
class MutantExecutionResult
{
    private $mutationHash;
    private $processCommandLine;
    private $processOutput;
    private $processResultCode;
    private $mutationDiff;
    private $mutatorName;
    private $originalFilePath;
    private $originalStartingLine;
    private $originalEndingLine;
    private $originalStartingColumn;
    private $originalEndingColumn;

    public function __construct(
        string $mutationHash,
        string $processCommandLine,
        string $processOutput,
        int $processResultCode,
        string $mutationDiff,
        string $mutatorName,
        string $originalFilePath,
        int $originalStartingLine,
        int $originalEndingLine,
        int $originalStartingColumn,
        int $originalEndingColumn
    ) {
        Assert::oneOf($processResultCode, MutantProcess::RESULT_CODES);
        Assert::oneOf($mutatorName, array_keys(ProfileList::ALL_MUTATORS));

        $this->mutationHash = $mutationHash;
        $this->processCommandLine = $processCommandLine;
        $this->processOutput = $processOutput;
        $this->processResultCode = $processResultCode;
        $this->mutationDiff = $mutationDiff;
        $this->mutatorName = $mutatorName;
        $this->originalFilePath = $originalFilePath;
        $this->originalStartingLine = $originalStartingLine;
        $this->originalEndingLine = $originalEndingLine;
        $this->originalStartingColumn = $originalStartingColumn;
        $this->originalEndingColumn = $originalEndingColumn;
    }

    public static function createFromNonCoveredMutant(Mutant $mutant): self
    {
        $mutation = $mutant->getMutation();

        return new self(
            $mutation->getHash(),
            '',
            '',
            MutantProcess::CODE_NOT_COVERED,
            $mutant->getDiff(),
            $mutant->getMutation()->getMutatorName(),
            $mutation->getOriginalFilePath(),
            $mutation->getOriginalStartingLine(),
            $mutation->getOriginalEndingLine(),
            $mutation->getOriginalStartingColumn(),
            $mutation->getOriginalEndingColumn()
        );
    }

    public static function createFromProcess(MutantProcess $mutantProcess): self
    {
        $process = $mutantProcess->getProcess();
        $mutant = $mutantProcess->getMutant();
        $mutation = $mutant->getMutation();

        return new self(
            $mutation->getHash(),
            $process->getCommandLine(),
            $process->isStarted() ? $process->getOutput() : '',
            $mutantProcess->getResultCode(),
            $mutant->getDiff(),
            $mutation->getMutatorName(),
            $mutantProcess->getOriginalFilePath(),
            $mutation->getOriginalStartingLine(),
            $mutation->getOriginalEndingLine(),
            $mutation->getOriginalStartingColumn(),
            $mutation->getOriginalEndingColumn()
        );
    }

    public function getMutationHash(): string
    {
        return $this->mutationHash;
    }

    public function getProcessCommandLine(): string
    {
        return $this->processCommandLine;
    }

    public function getProcessOutput(): string
    {
        return $this->processOutput;
    }

    public function getProcessResultCode(): int
    {
        return $this->processResultCode;
    }

    public function getMutationDiff(): string
    {
        return $this->mutationDiff;
    }

    public function getMutatorName(): string
    {
        return $this->mutatorName;
    }

    public function getOriginalFilePath(): string
    {
        return $this->originalFilePath;
    }

    public function getOriginalStartingLine(): int
    {
        return $this->originalStartingLine;
    }

    public function getOriginalEndingLine(): int
    {
        return $this->originalEndingLine;
    }

    public function getOriginalStartingColumn(): int
    {
        return $this->originalStartingColumn;
    }

    public function getOriginalEndingColumn(): int
    {
        return $this->originalEndingColumn;
    }
}
