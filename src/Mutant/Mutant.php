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

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\Mutation\Mutation;
use Later\Interfaces\Deferred;

/**
 * @internal
 * @final
 */
class Mutant
{
    /**
     * @param Deferred<string> $mutatedCode
     * @param Deferred<string> $diff
     * @param Deferred<string> $prettyPrintedOriginalCode
     */
    public function __construct(private readonly string $mutantFilePath, private readonly Mutation $mutation, private readonly Deferred $mutatedCode, private readonly Deferred $diff, private readonly Deferred $prettyPrintedOriginalCode)
    {
    }

    public function getFilePath(): string
    {
        return $this->mutantFilePath;
    }

    public function getMutation(): Mutation
    {
        return $this->mutation;
    }

    /**
     * @return Deferred<string>
     */
    public function getMutatedCode(): Deferred
    {
        return $this->mutatedCode;
    }

    /**
     * @return Deferred<string>
     */
    public function getPrettyPrintedOriginalCode(): Deferred
    {
        return $this->prettyPrintedOriginalCode;
    }

    /**
     * @return Deferred<string>
     */
    public function getDiff(): Deferred
    {
        return $this->diff;
    }

    public function isCoveredByTest(): bool
    {
        return $this->mutation->isCoveredByTest();
    }

    /**
     * @return TestLocation[]
     */
    public function getTests(): array
    {
        return $this->mutation->getAllTests();
    }
}
