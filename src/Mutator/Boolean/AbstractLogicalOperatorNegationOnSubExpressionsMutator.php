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

use Infection\Mutator\GetMutatorName;
use Infection\Mutator\Mutator;
use Infection\Mutator\Util\NegateExpression;
use PhpParser\Node;

abstract class AbstractLogicalOperatorNegationOnSubExpressionsMutator implements Mutator
{
    use GetMutatorName;
    use NegateExpression;

    /**
     * It creates new instance of specific logical operator
     */
    abstract protected function createNewInstance(Node\Expr $left, Node\Expr $right, array $attributes): Node\Expr;

    /**
     * It returns bool value whether given node is of specific logical operator type
     */
    abstract protected function instanceof(Node $node): bool;

    /**
     * @param Node\Expr $expr
     * @param Node\Expr[] $expressions
     * @return Node\Expr[]
     */
    protected function explodeExpressions(Node\Expr $expr, array &$expressions = []): array
    {
        if ($this->instanceof($expr->left)) {
            $this->explodeExpressions($expr->left, $expressions);
        } else {
            $expressions[] = $expr->left;
        }

        if ($this->instanceof($expr->right)) {
            $this->explodeExpressions($expr->right, $expressions);
        } else {
            $expressions[] = $expr->right;
        }

        return $expressions;
    }

    /**
     * @param Node\Expr[] $expressions
     * @param array $attributes
     * @return Node\Expr
     */
    protected function implode(array $expressions, array $attributes): Node\Expr
    {
        $chunk = [];

        while ($expression = array_shift($expressions)) {
            $chunk[] = $expression;

            if (count($chunk) === 2) {
                $chunk = [$this->mergeArrayOfTwoExpressions($chunk, $attributes)];
            }
        }

        return reset($chunk);
    }

    private function mergeArrayOfTwoExpressions(array $expressions, array $attributes): Node\Expr
    {
        [$left, $right] = $expressions;

        return $this->createNewInstance($left, $right, $attributes);
    }
}
