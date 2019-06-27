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

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;

/**
 * @internal
 */
final class IntegerNegation extends Mutator
{
    /**
     * Replaces any integer with negated integer value.
     * Replaces "-5" with "5"
     *
     * @param Node&Node\Stmt\Return_ $node
     *
     * @return Node\Stmt\Return_
     */
    public function mutate(Node $node)
    {
        return new Node\Stmt\Return_(
            new Node\Scalar\LNumber(-1 * $this->getIntegerValueOfNode($node), $node->getAttributes())
        );
    }

    protected function mutatesNode(Node $node): bool
    {
        if (!$node instanceof Node\Stmt\Return_) {
            return false;
        }

        $expr = $node->expr;

        if ($expr instanceof Node\Expr\UnaryMinus) {
            $expr = $expr->expr;
        }

        if (!$expr instanceof Node\Scalar\LNumber) {
            return false;
        }

        if ($expr->value === 0) {
            return false;
        }

        return true;
    }

    /**
     * @param Node&Node\Stmt\Return_ $node
     */
    private function getIntegerValueOfNode(Node $node): int
    {
        /** @var Node\Expr\UnaryMinus|Node\Scalar\LNumber $expression */
        $expression = $node->expr;

        if ($expression instanceof Node\Expr\UnaryMinus) {
            /** @var Node\Scalar\LNumber $innerExpression */
            $innerExpression = $expression->expr;

            return -$innerExpression->value;
        }

        return $expression->value;
    }
}
