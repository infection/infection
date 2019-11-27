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
use Infection\Visitor\ReflectionVisitor;
use function is_string;
use PhpParser\Node;

/**
 * @internal
 */
final class ArrayOneItem extends Mutator
{
    /**
     * Leaves only one item in the returned array
     *
     * Replaces "return $collection;" with "return count($collection) > 1 ? array_slice($collection, 0, 1, true) : $collection;"
     *
     * @param Node&Node\Stmt\Return_ $node
     *
     * @return Node\Stmt\Return_
     */
    public function mutate(Node $node)
    {
        /** @var Node\Expr\Variable $expression */
        $expression = $node->expr;

        $arrayVariable = new Node\Expr\Variable($expression->name);

        return new Node\Stmt\Return_(
            new Node\Expr\Ternary(
                new Node\Expr\BinaryOp\Greater(
                    new Node\Expr\FuncCall(new Node\Name('count'), [new Node\Arg($arrayVariable)]),
                    new Node\Scalar\LNumber(1)
                ),
                new Node\Expr\FuncCall(new Node\Name('array_slice'), [
                    new Node\Arg($arrayVariable),
                    new Node\Arg(new Node\Scalar\LNumber(0)),
                    new Node\Arg(new Node\Scalar\LNumber(1)),
                    new Node\Arg(new Node\Expr\ConstFetch(new Node\Name('true'))),
                ]),
                $arrayVariable
            )
        );
    }

    protected function mutatesNode(Node $node): bool
    {
        if (!$node instanceof Node\Stmt\Return_) {
            return false;
        }

        if (!$node->expr instanceof Node\Expr\Variable) {
            return false;
        }

        return $this->returnTypeIsArray($node);
    }

    private function returnTypeIsArray(Node $node): bool
    {
        /** @var \PhpParser\Node\Stmt\Function_|null $functionScope */
        $functionScope = $node->getAttribute(ReflectionVisitor::FUNCTION_SCOPE_KEY, null);

        if (null === $functionScope) {
            return false;
        }

        $returnType = $functionScope->getReturnType();

        if ($returnType instanceof Node\Identifier) {
            $returnType = $returnType->name;
        }

        return is_string($returnType) && $returnType === 'array';
    }
}
