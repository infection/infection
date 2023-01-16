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

namespace Infection\Mutator\Boolean;

use Infection\Mutator\Definition;
use Infection\Mutator\GetMutatorName;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorCategory;
use PhpParser\Node;
use function get_class;
use function in_array;

/**
 * @internal
 *
 * @implements Mutator<Node\Expr\BinaryOp\BooleanOr>
 */
final class LogicalOr implements Mutator
{
    use GetMutatorName;

    public static function getDefinition(): ?Definition
    {
        return new Definition(
            'Replaces an OR operator (`||`) with an AND operator (`&&`).',
            MutatorCategory::ORTHOGONAL_REPLACEMENT,
            null,
            <<<'DIFF'
- $a = $b || $c;
+ $a = $b && $c;
DIFF
        );
    }

    /**
     * @psalm-mutation-free
     *
     * Replaces "||" with "&&"
     *
     * @return iterable<Node\Expr\BinaryOp\BooleanAnd>
     */
    public function mutate(Node $node): iterable
    {
        yield new Node\Expr\BinaryOp\BooleanAnd($node->left, $node->right, $node->getAttributes());
    }

    public function canMutate(Node $node): bool
    {
        if (!$node instanceof Node\Expr\BinaryOp\BooleanOr) {
            return false;
        }

        $equalOp = [
            Node\Expr\BinaryOp\Identical::class,
            Node\Expr\BinaryOp\Equal::class,
        ];

        if (
            in_array(get_class($node->left), $equalOp, true) === true
            && in_array(get_class($node->right), $equalOp, true) === true
        ) {
            $varNameLeft = null;

            if ($node->left->left instanceof Node\Expr\Variable) {
                $varNameLeft = $node->left->left->name;
            } elseif ($node->left->right instanceof Node\Expr\Variable) {
                $varNameLeft = $node->left->right->name;
            }

            $varNameRight = null;

            if ($node->right->left instanceof Node\Expr\Variable) {
                $varNameRight = $node->right->left->name;
            } elseif ($node->right->right instanceof Node\Expr\Variable) {
                $varNameRight = $node->right->right->name;
            }

            return $varNameLeft !== $varNameRight;
        }

        $greaterOp = [
            Node\Expr\BinaryOp\Greater::class,
            Node\Expr\BinaryOp\GreaterOrEqual::class,
        ];

        $smallerOp = [
            Node\Expr\BinaryOp\Smaller::class,
            Node\Expr\BinaryOp\SmallerOrEqual::class,
        ];

        if (
            (
                in_array(get_class($node->left), $greaterOp, true) === true
                && in_array(get_class($node->right), $smallerOp, true) === true
            ) || (
                in_array(get_class($node->left), $smallerOp, true) === true
                && in_array(get_class($node->right), $greaterOp, true) === true
            )
        ) {
            $varNameLeft = null;

            if ($node->left->left instanceof Node\Expr\Variable) {
                $varNameLeft = $node->left->left->name;
            } elseif ($node->left->right instanceof Node\Expr\Variable) {
                $varNameLeft = $node->left->right->name;
            }

            $varNameRight = null;

            if ($node->right->left instanceof Node\Expr\Variable) {
                $varNameRight = $node->right->left->name;
            } elseif ($node->right->right instanceof Node\Expr\Variable) {
                $varNameRight = $node->right->right->name;
            }

            return $varNameLeft !== $varNameRight;
        }

        return true;
    }
}
