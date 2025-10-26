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

namespace Infection\Mutator\ReturnValue;

use Infection\Mutator\Definition;
use Infection\Mutator\MutatorCategory;
use Infection\Mutator\Util\AbstractValueToNullReturnValue;
use PhpParser\Node;

/**
 * @internal
 *
 * @extends AbstractValueToNullReturnValue<Node\Stmt\Return_>
 */
final class FunctionCall extends AbstractValueToNullReturnValue
{
    public static function getDefinition(): Definition
    {
        return new Definition(
            <<<'TXT'
                Replaces a returned evaluated function with `null` instead. The function evaluation statement is kept
                in order to preserve potential side effects. For example:

                ```php
                class X {
                    function foo()
                    {
                        return bar();
                    }
                }
                ```

                Will be mutated to:

                ```php
                class X {
                    function foo()
                    {
                        bar();
                        return null;
                    }
                }
                ```

                TXT
            ,
            MutatorCategory::ORTHOGONAL_REPLACEMENT,
            null,
            <<<'DIFF'
                class X {
                    function foo()
                    {
                -        return bar();
                +        bar();
                +        return null;
                    }
                }
                DIFF,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @return iterable<array<Node\Stmt\Expression|Node\Stmt\Return_>>
     */
    public function mutate(Node $node): iterable
    {
        /** @var Node\Expr\New_ $expr */
        $expr = $node->expr;

        yield [
            new Node\Stmt\Expression($expr),
            new Node\Stmt\Return_(
                new Node\Expr\ConstFetch(new Node\Name('null')),
            ),
        ];
    }

    public function canMutate(Node $node): bool
    {
        if (!$node instanceof Node\Stmt\Return_) {
            return false;
        }

        if (!$node->expr instanceof Node\Expr\FuncCall) {
            return false;
        }

        return $this->isNullReturnValueAllowed($node);
    }
}
