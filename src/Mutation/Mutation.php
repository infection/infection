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

use function array_keys;
use function count;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\Mutator\ProfileList;
use Webmozart\Assert\Assert;

/**
 * @internal
 * @final
 */
class Mutation
{
    private $originalFilePath;
    private $mutatorName;
    private $originalStartingLine;
    private $tests;
    private $executedByTests;
    private $mutationHash;
    private $mutationFilePath;
    private $mutatedCode;
    private $diff;

    /**
     * @param TestLocation[] $tests
     */
    public function __construct(
        string $originalFilePath,
        string $mutatorName,
        int $originalStartingLine,
        array $tests,
        string $mutationHash,
        string $mutationFilePath,
        string $mutatedCode,
        string $diff
    ) {
        Assert::oneOf($mutatorName, array_keys(ProfileList::ALL_MUTATORS));

        $this->originalFilePath = $originalFilePath;
        $this->mutatorName = $mutatorName;
        $this->originalStartingLine = $originalStartingLine;
        $this->tests = $tests;
        $this->executedByTests = count($tests) > 0;
        $this->mutationHash = $mutationHash;
        $this->mutationFilePath = $mutationFilePath;
        $this->mutatedCode = $mutatedCode;
        $this->diff = $diff;
    }

    public function getOriginalFilePath(): string
    {
        return $this->originalFilePath;
    }

    public function getMutatorName(): string
    {
        return $this->mutatorName;
    }

    public function getOriginalStartingLine(): int
    {
        return $this->originalStartingLine;
    }

    public function hasTests(): bool
    {
        return $this->executedByTests;
    }

    /**
     * @return TestLocation[]
     */
    public function getTests(): array
    {
        return $this->tests;
    }

    public function getHash(): string
    {
        return $this->mutationHash;
    }

    public function getFilePath(): string
    {
        return $this->mutationFilePath;
    }

    public function getMutatedCode(): string
    {
        return $this->mutatedCode;
    }

    public function getDiff(): string
    {
        return $this->diff;
    }
}
