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

namespace Infection\Mutator\Sort;

use Infection\Mutator\Definition;
use Infection\Mutator\GetMutatorName;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorCategory;
use function is_numeric;
use PhpParser\Node;

/**
 * @internal
 *
 * @implements Mutator<Node\Expr\BinaryOp\Spaceship>
 */
final class Spaceship implements Mutator
{
    use GetMutatorName;

    public static function getDefinition(): Definition
    {
        return new Definition(
            <<<'TXT'
                Swaps the spaceship operator (`<=>`) operands, e.g. replaces `$a <=> $b` with `$b <=> $a`.
                TXT
            ,
            MutatorCategory::ORTHOGONAL_REPLACEMENT,
            null,
            <<<'DIFF'
                - $a = $b <=> $c;
                + $a = $c <=> $b;
                DIFF,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @return iterable<Node\Expr\BinaryOp\Spaceship>
     */
    public function mutate(Node $node): iterable
    {
        yield new Node\Expr\BinaryOp\Spaceship($node->right, $node->left);
    }

    public function canMutate(Node $node): bool
    {
        if (!$node instanceof Node\Expr\BinaryOp\Spaceship) {
            return false;
        }

        if ($this->isCompareWithZero($node)) {
            return false;
        }

        return true;
    }

    private function isCompareWithZero(Node\Expr\BinaryOp\Spaceship $node): bool
    {
        $parentAttribute = $node->getAttribute('parent');

        if ($parentAttribute instanceof Node\Expr\BinaryOp\Identical) {
            return $this->isIntegerScalarEqualToZero($parentAttribute);
        }

        if ($parentAttribute instanceof Node\Expr\BinaryOp\Equal) {
            return $this->isEqualToZero($parentAttribute);
        }

        return false;
    }

    private function isIntegerScalarEqualToZero(Node\Expr\BinaryOp $node): bool
    {
        if (!$node instanceof Node\Expr\BinaryOp\Identical && !$node instanceof Node\Expr\BinaryOp\Equal) {
            return false;
        }

        if ($node->right instanceof Node\Scalar\LNumber && $node->right->value === 0) {
            return true;
        }

        if ($node->left instanceof Node\Scalar\LNumber && $node->left->value === 0) {
            return true;
        }

        return false;
    }

    private function isEqualToZero(Node\Expr\BinaryOp\Equal $node): bool
    {
        if ($this->isIntegerScalarEqualToZero($node)) {
            return true;
        }

        if ($node->right instanceof Node\Scalar\DNumber && $node->right->value === 0.0) {
            return true;
        }

        if ($node->left instanceof Node\Scalar\DNumber && $node->left->value === 0.0) {
            return true;
        }

        if (
            $node->right instanceof Node\Scalar\String_
            && is_numeric($node->right->value)
            && ($node->right->value === '0' || $node->right->value === '0.0')
        ) {
            return true;
        }

        if (
            $node->left instanceof Node\Scalar\String_
            && is_numeric($node->left->value)
            && ($node->left->value === '0' || $node->left->value === '0.0')
        ) {
            return true;
        }

        return false;
    }
}
