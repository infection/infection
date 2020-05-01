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

namespace Infection\Mutator;

use Infection\CannotBeInstantiated;

/**
 * @internal
 */
final class MutatorCategory
{
    use CannotBeInstantiated;

    /**
     * Semantic reductions exposes unused semantics. For example:
     *
     * ```php
     * $x = $a + $b;
     *
     * // to
     *
     * $x = $a;
     * ```
     *
     * If the semantics are unneeded, they should be removed. Otherwise they should be tested.
     */
    public const SEMANTIC_REDUCTION = 'semanticReduction';

    /**
     * Semantic additions are a peculiar case and usually do not make sense. For example:
     *
     * ```php
     * $x = $a === $b;
     *
     * // to
     *
     * $x = $a == $b;
     * ```
     *
     * More information on how a mutator of this category should be tackled should be provided in
     * the mutator definition.
     */
    public const SEMANTIC_ADDITION = 'semanticAddition';

    /**
     * An example of orthogonal replacement is:
     *
     * ```php
     * $a > $b;
     *
     * // to
     *
     * $a < $b;
     * ```
     *
     * Neither form has less semantics than the other. It is however a mutation that shows a lack
     * of coverage.
     */
    public const ORTHOGONAL_REPLACEMENT = 'orthogonalReplacement';

    // Also known but unused for now: neutral, semantic addition

    public const ALL = [
        self::SEMANTIC_REDUCTION,
        self::SEMANTIC_ADDITION,
        self::ORTHOGONAL_REPLACEMENT,
    ];
}
