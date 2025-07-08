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

namespace Infection\Mutator\Removal;

use Infection\Mutator\Definition;
use Infection\Mutator\GetMutatorName;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorCategory;
use Infection\PhpParser\Visitor\NextConnectingVisitor;
use Infection\PhpParser\Visitor\ReflectionVisitor;
use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use Webmozart\Assert\Assert;

/**
 * @internal
 *
 * @implements Mutator<Node\Stmt\Return_>
 */
final class ReturnRemoval implements Mutator
{
    use GetMutatorName;

    private const VOID = 'void';

    public static function getDefinition(): Definition
    {
        return new Definition(
            'Removes a return statement.',
            MutatorCategory::SEMANTIC_REDUCTION,
            null,
            <<<'DIFF'
                - return $foo;
                DIFF,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @return iterable<Node\Stmt\Nop>
     */
    public function mutate(Node $node): iterable
    {
        yield new Node\Stmt\Nop();
    }

    public function canMutate(Node $node): bool
    {
        if (!$node instanceof Node\Stmt\Return_) {
            return false;
        }

        // Any return statement in a function-like node that does not have a return type can be removed.
        if (!self::hasReturnType($node)) {
            return true;
        }

        // If there's more after this return statement, we can remove it
        return self::hasNextNode($node);
    }

    protected function hasReturnType(Node $node): bool
    {
        $functionScope = ReflectionVisitor::getFunctionScope($node);

        // We do not expect to see a return statement outside a function-like node.
        Assert::isInstanceOf($functionScope, FunctionLike::class);

        $returnType = $functionScope->getReturnType();

        // A void return type is the same as no return type for this mutator.
        if (
            $returnType === null
            || $returnType->toLowerString() === self::VOID
        ) {
            return false;
        }

        return true;
    }

    private static function hasNextNode(Node $node): bool
    {
        return $node->getAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE) !== null;
    }
}
