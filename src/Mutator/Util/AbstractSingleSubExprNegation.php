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
use PhpParser\Node;

/**
 * @internal
 *
 * @template TNode of Node
 * @implements Mutator<TNode>
 */
abstract class AbstractSingleSubExprNegation implements Mutator
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
        if (!$this->isSupportedNode($node)) {
            return false;
        }

        $parent = ParentConnector::findParent($node);

        return $parent !== null && !$this->isSupportedNode($parent); // only grandparent
    }

    /**
     * @return class-string<Node\Expr\BinaryOp\BooleanOr|Node\Expr\BinaryOp\BooleanAnd>
     */
    abstract protected function getSupportedBinaryOpExprClass(): string;

    private function isSupportedBinaryOpExpr(Node\Expr\BinaryOp $node): bool
    {
        $class = $this->getSupportedBinaryOpExprClass();

        return $node instanceof $class;
    }

    private function countSubExpressionsToNegate(Node\Expr $node, int &$count = 0): int
    {
        if ($this->isSupportedNode($node)) {
            /** @var Node\Expr\BinaryOp $binaryOpNode */
            $binaryOpNode = $node;

            $this->countSubExpressionsToNegate($binaryOpNode->left, $count);
            $this->countSubExpressionsToNegate($binaryOpNode->right, $count);
        } elseif ($this->isSingleOperandExpression($node)) {
            ++$count;
        }

        return $count;
    }

    private function negateSubExpression(Node\Expr $node, int $negateExpressionAtIndex, int &$currentExpressionIndex = 0): Node\Expr
    {
        if ($this->isSupportedNode($node)) {
            /** @var Node\Expr\BinaryOp $binaryOpNode */
            $binaryOpNode = $node;

            $binaryOpExprClass = $this->getSupportedBinaryOpExprClass();

            return new $binaryOpExprClass(
                $this->negateSubExpression($binaryOpNode->left, $negateExpressionAtIndex, $currentExpressionIndex),
                $this->negateSubExpression($binaryOpNode->right, $negateExpressionAtIndex, $currentExpressionIndex),
                $node->getAttributes(),
            );
        }

        if ($this->isSingleOperandExpression($node)) {
            if ($currentExpressionIndex === $negateExpressionAtIndex) {
                ++$currentExpressionIndex;

                return new Node\Expr\BooleanNot($node);
            }

            ++$currentExpressionIndex;
        }

        return $node;
    }

    private function isSupportedNode(Node $node): bool
    {
        return $node instanceof Node\Expr\BinaryOp && $this->isSupportedBinaryOpExpr($node);
    }
}
