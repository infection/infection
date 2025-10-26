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

namespace Infection\Mutator\Regex;

use Generator;
use Infection\Mutator\Definition;
use Infection\Mutator\MutatorCategory;
use function Safe\preg_match;

/**
 * @internal
 */
final class PregMatchRemoveDollar extends AbstractPregMatch
{
    public const ANALYSE_REGEX = '/^([^\w\s\\\\])([^$]*)([$]?)\1([gmixXsuUAJD]*)$/';

    public static function getDefinition(): Definition
    {
        return new Definition(
            <<<'TXT'
                Removes a "$" character from a regular expression in `preg_match()`. For example:

                ```php
                preg_match('/^test$/', $string);
                ```

                Will be mutated to:

                ```php
                preg_match('/^test/', $string);
                ```

                TXT
            ,
            MutatorCategory::SEMANTIC_REDUCTION,
            null,
            <<<'DIFF'
                - preg_match('/^test$/', $string);
                + preg_match('/^test/', $string);
                DIFF,
        );
    }

    /**
     * @psalm-mutation-free
     */
    protected function mutateRegex(string $regex): Generator
    {
        preg_match(self::ANALYSE_REGEX, $regex, $matches);

        $delimiter = $matches[1] ?? '';
        $regexBody = $matches[2] ?? '';
        $flags = $matches[4] ?? '';

        yield $delimiter . $regexBody . $delimiter . $flags;
    }

    protected function isProperRegexToMutate(string $regex): bool
    {
        preg_match(self::ANALYSE_REGEX, $regex, $matches);

        return ($matches[3] ?? null) === '$';
    }
}
