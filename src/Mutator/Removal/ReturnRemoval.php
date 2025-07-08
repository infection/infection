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
use PhpParser\Node\ComplexType;
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

        $functionScope = ReflectionVisitor::getFunctionScope($node);
        Assert::isInstanceOf($functionScope, FunctionLike::class);

        $returnType = $functionScope->getReturnType();

        // Check if it's a void return type
        if ($returnType !== null && !($returnType instanceof ComplexType) && $returnType->toLowerString() === self::VOID) {
            // In void functions, any return statement can be removed
            return true;
        }

        // Check if there's a non-void return type defined
        if (self::hasNonVoidReturnType($returnType)) {
            // For functions with return types, we can remove it only if there's more after this return
            return self::hasNextStmtNode($node);
        }

        // For functions without return types, we can only remove the return if:
        // 1. There's another statement after it, OR
        // 2. It returns a non-null value (not return; or return null;)
        return self::hasNextStmtNode($node) || !self::isNullReturn($node);
    }

    private static function isNullReturn(Node\Stmt\Return_ $node): bool
    {
        // Empty return (return;)
        if ($node->expr === null) {
            return true;
        }

        // Check for return null;
        if ($node->expr instanceof Node\Expr\ConstFetch) {
            return $node->expr->name->toLowerString() === 'null';
        }

        return false;
    }

    private static function hasNonVoidReturnType($returnType): bool
    {
        // No return type
        if ($returnType === null) {
            return false;
        }

        // Complex types are specific return types
        if ($returnType instanceof ComplexType) {
            return true;
        }

        // Void is not considered a "real" return type for our purposes
        if ($returnType->toLowerString() === self::VOID) {
            return false;
        }

        return true;
    }

    private static function hasNextStmtNode(Node $node): bool
    {
        return $node->getAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE) !== null;
    }
}
