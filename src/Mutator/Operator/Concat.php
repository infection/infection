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

namespace Infection\Mutator\Operator;

use Infection\Mutator\Definition;
use Infection\Mutator\GetMutatorName;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorCategory;
use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;

/**
 * @internal
 *
 * @implements Mutator<Node\Expr\BinaryOp\Concat>
 */
final class Concat implements Mutator
{
    use GetMutatorName;

    public static function getDefinition(): Definition
    {
        return new Definition(
            <<<'TXT'
                Flips the operands of the string concatenation operator `.`. For example:

                ```php
                'foo' . 'bar';
                ```

                Will be mutated to:

                ```php
                'bar' . 'foo';
                ```
                TXT
            ,
            MutatorCategory::ORTHOGONAL_REPLACEMENT,
            null,
            <<<'DIFF'
                - 'foo' . 'bar';
                + 'bar' . 'foo';
                DIFF,
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function mutate(Node $node): iterable
    {
        $printer = new Standard();

        if ($node->left instanceof Node\Expr\BinaryOp\Concat) {
            $left = new Node\Expr\BinaryOp\Concat($node->left->left, $node->right);
            $right = $node->left->right;
        } else {
            [$left, $right] = [$node->right, $node->left];
        }

        $newNode = new Node\Expr\BinaryOp\Concat($left, $right);

        if ($printer->prettyPrint([clone $node]) !== $printer->prettyPrint([$newNode])) {
            yield $newNode;
        }
    }

    public function canMutate(Node $node): bool
    {
        return $node instanceof Node\Expr\BinaryOp\Concat;
    }
}
