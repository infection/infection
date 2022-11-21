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
use Infection\Mutator\Util\Visitor;
use Infection\PhpParser\Visitor\ParentConnector;
use PhpParser\Node;
use PhpParser\NodeTraverser;

final class LogicalAndNegateSingleSubExpression implements Mutator
{
    use GetMutatorName;

    public static function getDefinition(): ?Definition
    {
        return new Definition(
            <<<'TXT'
Negates all sub-expressions separately in AND (`&&`). No matter how many sub-expressions, but all should be connected with AND (`&&`) operator.
TXT
            ,
            MutatorCategory::ORTHOGONAL_REPLACEMENT,
            null,
            <<<'DIFF'
- $a = $b && $c;
# Mutation 1
+ $a = !$b && $c;
# Mutation 2
+ $a = $b && !$c;
DIFF
        );
    }

    /**
     * @param Node\Expr\BinaryOp\BooleanAnd $node
     * @return iterable
     */
    public function mutate(Node $node): iterable
    {
        $counter = new Visitor\LogicalAnd\CountSubExpressionsToNegateVisitor;

        $traverser = new NodeTraverser;
        $traverser->addVisitor($counter);
        $traverser->traverse([$node]);

        for ($i = 0; $i < $counter->getCount(); $i++) {
            $traverser = new NodeTraverser();
            $traverser->addVisitor(new Visitor\LogicalAnd\NegateOnlySingleSubExpressionVisitor($i));

            yield from $traverser->traverse([clone $node]);
        }
    }

    public function canMutate(Node $node): bool
    {
        if (!$node instanceof Node\Expr\BinaryOp\BooleanAnd) {
            return false;
        }

        $parent = ParentConnector::findParent($node);

        return $parent !== null && !$parent instanceof Node\Expr\BinaryOp\BooleanAnd; // only grandparent
    }
}
