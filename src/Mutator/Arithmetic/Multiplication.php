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

namespace Infection\Mutator\Arithmetic;

use Infection\Mutator\Definition;
use Infection\Mutator\GetMutatorName;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorCategory;
use Infection\PhpParser\Visitor\ParentConnector;
use Infection\PhpParser\Visitor\ReflectionVisitor;
use function is_string;
use PhpParser\Node;

/**
 * @internal
 *
 * @implements Mutator<Node\Expr\BinaryOp\Mul>
 */
final class Multiplication implements Mutator
{
    use GetMutatorName;

    public static function getDefinition(): Definition
    {
        return new Definition(
            <<<'TXT'
                Replaces a multiplication operator (`*`) with a division assignment operator (`/`).
                TXT
            ,
            MutatorCategory::ORTHOGONAL_REPLACEMENT,
            null,
            <<<'DIFF'
                - $a = $b * $c;
                + $a = $b / $c;
                DIFF,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @return iterable<Node\Expr\BinaryOp\Div>
     */
    public function mutate(Node $node): iterable
    {
        yield new Node\Expr\BinaryOp\Div($node->left, $node->right, $node->getAttributes());
    }

    public function canMutate(Node $node): bool
    {
        if (!$node instanceof Node\Expr\BinaryOp\Mul) {
            return false;
        }

        if ($this->isNumericOne($node->left) || $this->isNumericOne($node->right)) {
            return false;
        }

        if ($node->left instanceof Node\Expr\UnaryMinus && $this->isNumericOne($node->left->expr)) {
            return false;
        }

        if ($node->right instanceof Node\Expr\UnaryMinus && $this->isNumericOne($node->right->expr)) {
            return false;
        }

        $functionScope = $node->getAttribute(ReflectionVisitor::FUNCTION_SCOPE_KEY);

        if (!$functionScope instanceof Node\Stmt\ClassMethod) {
            return true;
        }

        $parentNode = ParentConnector::getParent($node);

        if (!$parentNode instanceof Node\Stmt\Return_) {
            return true;
        }

        $returnType = $functionScope->getReturnType();

        if ($returnType instanceof Node\Identifier) {
            $returnType = $returnType->name;
        }

        return !(is_string($returnType) && $returnType === 'int');
    }

    private function isNumericOne(Node $node): bool
    {
        if ($node instanceof Node\Scalar\LNumber && $node->value === 1) {
            return true;
        }

        return $node instanceof Node\Scalar\DNumber && $node->value === 1.0;
    }
}
