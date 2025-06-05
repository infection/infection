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

use function array_key_exists;
use function in_array;
use Infection\Mutator\Definition;
use Infection\Mutator\GetMutatorName;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorCategory;
use PhpParser\Node;
use PhpParser\Node\Expr;
use ReflectionFunction;
use ReflectionNamedType;

/**
 * @internal
 *
 * @deprecated This mutator is a semantic addition
 *
 * @implements Mutator<Node\Expr\BinaryOp\Identical>
 */
final class IdenticalEqual implements Mutator
{
    use GetMutatorName;

    /**
     * @var array<string, string|null>
     */
    private array $reflectionCache = [];

    public static function getDefinition(): Definition
    {
        return new Definition(
            <<<'TXT'
                Replaces a strict comparison (using the identical operator (`===`)) with a loose comparison (using
                the loose operator (`==`)).
                TXT
            ,
            MutatorCategory::SEMANTIC_ADDITION,
            null,
            <<<'DIFF'
                - $a = $b === $c;
                + $a = $b == $c;
                DIFF,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @return iterable<Expr\BinaryOp\Equal>
     */
    public function mutate(Node $node): iterable
    {
        yield new Expr\BinaryOp\Equal($node->left, $node->right, $node->getAttributes());
    }

    public function canMutate(Node $node): bool
    {
        if (!$node instanceof Expr\BinaryOp\Identical) {
            return false;
        }

        if (
            $node->left instanceof Expr\FuncCall
            && $node->right instanceof Expr\FuncCall
            && $this->isSameTypeIdenticalComparison($node->left, $node->right)
        ) {
            return false;
        }

        if (
            $node->left instanceof Expr\FuncCall
            && ($node->right instanceof Node\Scalar || $node->right instanceof Expr\ConstFetch)
            && $this->isSameTypeIdenticalComparison($node->left, $node->right)
        ) {
            return false;
        }

        if (
            $node->right instanceof Expr\FuncCall
            && ($node->left instanceof Node\Scalar || $node->left instanceof Expr\ConstFetch)
            && $this->isSameTypeIdenticalComparison($node->right, $node->left)
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param Node\Scalar|Expr\ConstFetch|Expr\FuncCall $expr
     */
    private function isSameTypeIdenticalComparison(Expr\FuncCall $call, Expr $expr): bool
    {
        $returnType = $this->getReturnType($call);

        if ($returnType === null) {
            return false;
        }

        if ($expr instanceof Node\Scalar\Int_) {
            return $returnType === 'int';
        }

        if ($expr instanceof Node\Scalar\String_) {
            return $returnType === 'string';
        }

        if ($expr instanceof Node\Scalar\Float_) {
            return $returnType === 'float';
        }

        if ($expr instanceof Expr\ConstFetch) {
            return in_array($expr->name->toString(), ['true', 'false'], true);
        }

        if ($expr instanceof Expr\FuncCall) {
            $exprReturnType = $this->getReturnType($expr);

            if ($exprReturnType === null) {
                return false;
            }

            return $returnType === $exprReturnType;
        }

        return false;
    }

    private function getReturnType(Expr\FuncCall $call): ?string
    {
        if (!$call->name instanceof Node\Name) {
            return null;
        }

        $name = $call->name->toString();

        if (array_key_exists($name, $this->reflectionCache)) {
            return $this->reflectionCache[$name];
        }

        $reflection = new ReflectionFunction($name);
        $returnType = $reflection->getReturnType();

        if (!$returnType instanceof ReflectionNamedType) {
            return $this->reflectionCache[$name] = null;
        }

        return $this->reflectionCache[$name] = $returnType->getName();
    }
}
