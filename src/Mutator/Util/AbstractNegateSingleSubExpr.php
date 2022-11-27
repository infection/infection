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

namespace Infection\Mutator\Util;

use Infection\Mutator\Mutator;
use Infection\Mutator\SimpleExpression;
use Infection\PhpParser\Visitor\ParentConnector;
use LogicException;
use PhpParser\Node;

/**
 * @internal
 *
 * @implements Mutator<Node>
 */
abstract class AbstractNegateSingleSubExpr implements Mutator
{
    use SimpleExpression;

    /**
     * @psalm-mutation-free
     *
     * @param Node\Expr $node
     */
    public function mutate(Node $node): iterable
    {
        $subExpressionsToNegateCount = $this->countSubExpressionsToNegate($node);

        for ($expressionIndex = 0; $expressionIndex < $subExpressionsToNegateCount; ++$expressionIndex) {
            yield $this->negateSubExpression($node, $expressionIndex);
        }
    }

    public function canMutate(Node $node): bool
    {
        if (!$this->isInstanceOf($node)) {
            return false;
        }

        $parent = ParentConnector::findParent($node);

        return $parent !== null && !$this->isInstanceOf($parent); // only grandparent
    }

    abstract protected function isInstanceOf(Node $node): bool;

    /**
     * @param array<string, mixed> $attributes
     */
    abstract protected function create(Node\Expr $left, Node\Expr $right, array $attributes): Node\Expr;

    private function countSubExpressionsToNegate(Node\Expr $node, int &$count = 0): int
    {
        if ($this->isInstanceOf($node)) {
            $left = $node->left ?? throw new LogicException('Node should contains left attribute');
            $right = $node->right ?? throw new LogicException('Node should contains right attribute');

            $this->countSubExpressionsToNegate($left, $count);
            $this->countSubExpressionsToNegate($right, $count);
        } elseif ($this->isSimpleExpression($node)) {
            ++$count;
        }

        return $count;
    }

    private function negateSubExpression(Node\Expr $node, int $negateExpressionAtIndex, int &$currentExpressionIndex = 0): Node\Expr
    {
        if ($this->isInstanceOf($node)) {
            $left = $node->left ?? throw new LogicException('Node should contains left attribute');
            $right = $node->right ?? throw new LogicException('Node should contains right attribute');

            return $this->create(
                $this->negateSubExpression($left, $negateExpressionAtIndex, $currentExpressionIndex),
                $this->negateSubExpression($right, $negateExpressionAtIndex, $currentExpressionIndex),
                $node->getAttributes(),
            );
        }

        if ($this->isSimpleExpression($node)) {
            if ($currentExpressionIndex === $negateExpressionAtIndex) {
                ++$currentExpressionIndex;

                return new Node\Expr\BooleanNot($node);
            }

            ++$currentExpressionIndex;
        }

        return $node;
    }
}
