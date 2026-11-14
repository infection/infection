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

use Symfony\Component\Console\Input\InputOption;

/**
 * Immutable, ordered command-line arguments. Transformations preserve unknown
 * tokens and interpret only explicitly supplied option definitions. Definitions
 * describe required-value or value-less options; an unknown option's separate
 * value cannot be distinguished from another option.
 *
 * @internal
 */
interface CommandLineArguments
{
    /**
     * Retains option definitions for subsequent transformations.
     */
    public function withOptionDefinitions(InputOption ...$options): self;

    /**
     * Removes the specified options and their values, retaining other definitions.
     */
    public function withoutOptions(InputOption ...$options): self;

    public function hasOption(InputOption $option): bool;

    /**
     * Adds tokens before the option terminator. Retained definitions distinguish
     * known option values equal to "--" from the terminator.
     *
     * @param list<string> $tokens
     */
    public function append(array $tokens): self;

    /**
     * @param list<string> $tokens
     */
    public function prepend(array $tokens): self;

    /**
     * @return list<string>
     */
    public function toTokens(): array;

    /**
     * Serializes with double quotes and escapes following the tokenizer's grammar,
     * not shell syntax or legacy adapters' space splitting.
     */
    public function toString(): string;
}
