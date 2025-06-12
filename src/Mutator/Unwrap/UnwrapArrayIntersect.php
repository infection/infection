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

namespace Infection\Mutator\Unwrap;

use function array_keys;
use Infection\Mutator\Definition;
use Infection\Mutator\MutatorCategory;
use PhpParser\Node;

/**
 * @internal
 */
final class UnwrapArrayIntersect extends AbstractFunctionUnwrapMutator
{
    public static function getDefinition(): Definition
    {
        return new Definition(
            <<<'TXT'
                Replaces an `array_intersect` function call with its operands. For example:

                ```php
                $x = array_intersect($array1, $array2);
                ```

                Will be mutated to:

                ```php
                $x = $array1;
                ```

                And:

                ```php
                $x = $array2;
                ```

                TXT
            ,
            MutatorCategory::SEMANTIC_REDUCTION,
            null,
            <<<'DIFF'
                - $x = array_intersect($array1, $array2);
                # Mutation 1
                + $x = $array1;
                # Mutation 2
                + $x = $array2;
                DIFF,
        );
    }

    protected function getFunctionName(): string
    {
        return 'array_intersect';
    }

    /**
     * @psalm-mutation-free
     */
    protected function getParameterIndexes(Node\Expr\FuncCall $node): iterable
    {
        yield from array_keys($node->args);
    }
}
