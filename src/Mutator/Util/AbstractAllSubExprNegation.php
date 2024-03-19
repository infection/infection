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

use function in_array;
use Infection\Mutator\GetMutatorName;
use Infection\Mutator\Mutator;
use Infection\PhpParser\Visitor\ParentConnector;
use PhpParser\Node;

/**
 * @internal
 *
 * @template TNode of Node
 * @implements Mutator<TNode>
 */
abstract class AbstractAllSubExprNegation implements Mutator
{
    use GetMutatorName;

    private const BOOLEANS = ['true', 'false'];

    /**
     * @psalm-mutation-free
     *
     * @param Node\Expr $node
     *
     * @return iterable<Node>
     */
    public function mutate(Node $node): iterable
    {
        yield $this->negateEverySubExpression($node);
    }

    public function canMutate(Node $node): bool
    {
        $supportedNodeClass = $this->getSupportedBinaryOpExprClass();

        if (!$node instanceof $supportedNodeClass) {
            return false;
        }

        $parent = ParentConnector::findParent($node);

        // only grandparent
        if ($parent === null || $parent instanceof $supportedNodeClass) {
            return false;
        }

        if ($this->allSubConditionsAreNotMutable($node->left, $node->right)) {
            return false;
        }

        return true;
    }

    /**
     * @return class-string<Node\Expr\BinaryOp\BooleanOr|Node\Expr\BinaryOp\BooleanAnd>
     */
    abstract protected function getSupportedBinaryOpExprClass(): string;

    private function negateEverySubExpression(Node\Expr|Node\Expr\BinaryOp\BooleanOr $node): Node\Expr
    {
        $supportedNodeClass = $this->getSupportedBinaryOpExprClass();

        if ($node instanceof $supportedNodeClass) {
            return new $supportedNodeClass(
                $this->negateEverySubExpression($node->left),
                $this->negateEverySubExpression($node->right),
                $node->getAttributes(),
            );
        }

        // do not mutate `$a === false` to `!($a === true)` as this is a duplicate of FalseValue mutator
        if ($this->isIdenticalComparisonWithBoolean($node)) {
            return $node;
        }

        return $node instanceof Node\Expr\BooleanNot ? $node->expr : new Node\Expr\BooleanNot($node);
    }

    /**
     * `false === $a` or `$a === true` or `$b !== true` etc.
     */
    private function isIdenticalComparisonWithBoolean(Node\Expr|Node\Expr\BinaryOp\BooleanOr $node): bool
    {
        if (!$node instanceof Node\Expr\BinaryOp\Identical && !$node instanceof Node\Expr\BinaryOp\NotIdentical) {
            return false;
        }

        return $this->isBoolean($node->left) || $this->isBoolean($node->right);
    }

    private function isBoolean(Node\Expr $node): bool
    {
        return $node instanceof Node\Expr\ConstFetch && in_array($node->name->toLowerString(), self::BOOLEANS, true);
    }

    /**
     * For example if all of them are identical comparisons: `$a === true && $b === false`, we shouldn't mutate
     */
    private function allSubConditionsAreNotMutable(Node\Expr $left, Node\Expr $right): bool
    {
        $leftIsNotMutable = ($left instanceof Node\Expr\BinaryOp\BooleanOr || $left instanceof Node\Expr\BinaryOp\BooleanAnd)
            ? $this->allSubConditionsAreNotMutable($left->left, $left->right)
            : $this->isIdenticalComparisonWithBoolean($left);

        // optimization: if left is mutable - no need to check right
        if (!$leftIsNotMutable) {
            return false;
        }

        return ($right instanceof Node\Expr\BinaryOp\BooleanOr || $right instanceof Node\Expr\BinaryOp\BooleanAnd)
            ? $this->allSubConditionsAreNotMutable($right->left, $right->right)
            : $this->isIdenticalComparisonWithBoolean($right);
    }
}
