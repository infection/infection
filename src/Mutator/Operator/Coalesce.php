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

/**
 * @internal
 *
 * @implements Mutator<Node\Expr\BinaryOp\Coalesce>
 */
final class Coalesce implements Mutator
{
    use GetMutatorName;

    public static function getDefinition(): Definition
    {
        return new Definition(
            <<<'TXT'
                Swaps the coalesce operator (`??`) operands,
                e.g. replaces `$a ?? $b` with `$b ?? $a` or `$a ?? $b ?? $c` with `$b ?? $a ?? $c` and `$a ?? $c ?? $b`.
                TXT
            ,
            MutatorCategory::ORTHOGONAL_REPLACEMENT,
            null,
            <<<'DIFF'
                - $d = $a ?? $b ?? $c;
                # Mutation 1
                + $d = $b ?? $a ?? $c;
                # Mutation 2
                + $d = $a ?? $c ?? $b;
                DIFF,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @return iterable<Node\Expr>
     */
    public function mutate(Node $node): iterable
    {
        $left = $node->left;
        $right = $node->right;

        if ($right instanceof Node\Expr\BinaryOp\Coalesce) {
            $left = new Node\Expr\BinaryOp\Coalesce($node->left, $right->right, $right->getAttributes());
            $right = $right->left;
        }

        yield new Node\Expr\BinaryOp\Coalesce($right, $left, $node->getAttributes());
    }

    public function canMutate(Node $node): bool
    {
        return $node instanceof Node\Expr\BinaryOp\Coalesce
            && !$node->left instanceof Node\Expr\ConstFetch
            && !$node->left instanceof Node\Expr\ClassConstFetch
            && !($node->right instanceof Node\Expr\ConstFetch && $node->right->name->toLowerString() === 'null');
    }
}
