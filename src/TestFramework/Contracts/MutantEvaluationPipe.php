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

namespace Infection\TestFramework\Contracts;

/**
 * Represents a mutant evaluation as a chain of processes that may require follow-up stages.
 * It exposes the current process and controls whether the evaluation advances to the next one.
 *
 * @internal
 */
interface MutantEvaluationPipe
{
    public function getMutant(): Mutant;

    /**
     * Whether evaluating the mutant has not started yet.
     */
    public function isCold(): bool;

    /**
     * Retrieving the current process may cause the pipe to no longer be cold.
     */
    public function getCurrent(): MutantProcess;

    /**
     * Determining whether a next process exists may cause the pipe to no longer be cold.
     */
    public function hasNext(): bool;

    /**
     * Creating the next process may cause the pipe to no longer be cold.
     */
    public function createNext(): MutantProcess;

    /**
     * Merges this pipe with the given pipe, preserving their evaluation order.
     *
     * Both pipes must be cold. The returned pipe is cold and neither input is mutated.
     */
    public function merge(self $other): self;
}
