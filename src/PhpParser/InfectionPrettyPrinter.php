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

namespace Infection\PhpParser;

use Override;
use PhpParser\Node\Scalar\String_;
use PhpParser\PrettyPrinter\Standard;
use function Safe\preg_replace;
use function str_starts_with;
use function strtr;
use function substr;

/**
 * @internal
 */
final class InfectionPrettyPrinter extends Standard
{
    /**
     * A mutated string keeps the attributes of the original one, including its raw value.
     * When the original source used PHP-Parser's own escaping (canonical '\\\\|'), print the
     * mutated string the same way, so the diff only shows what the mutator changed.
     */
    #[Override]
    protected function pScalar_String(String_ $node): string
    {
        /** @var string $rawValue */
        $rawValue = $node->getAttribute('rawValue', '');

        if ($node->getAttribute('kind') === String_::KIND_SINGLE_QUOTED && str_starts_with($rawValue, '\'')) {
            $originalValue = strtr(substr($rawValue, 1, -1), ['\\\\' => '\\', '\\\'' => '\'']);

            if (parent::pSingleQuotedString($originalValue) === $rawValue) {
                return parent::pSingleQuotedString($node->value);
            }
        }

        return parent::pScalar_String($node);
    }

    /**
     * Prints a single-quoted string using minimal escaping.
     *
     * PHP-Parser's default regex also escapes a backslash when it is preceded by
     * another backslash (the (?<=\\)\\ alternative), to avoid producing odd-looking
     * three-backslash sequences. That extra rule changes non-canonical but valid
     * source like '\\\|' (3 backslashes) to '\\\\|' (4 backslashes), making mutation
     * diffs noisier than necessary. Dropping that rule keeps the original encoding.
     */
    #[Override]
    protected function pSingleQuotedString(string $string): string
    {
        return '\'' . preg_replace('/\'|\\\\(?=[\'\\\\]|$)/', '\\\\$0', $string) . '\'';
    }
}
