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

namespace Infection\Mutator\Number;

use function in_array;
use Infection\Visitor\ParentConnectorVisitor;
use PhpParser\Node;

/**
 * @internal
 */
final class DecrementInteger extends AbstractNumberMutator
{
    private const COUNT_NAMES = [
        'count',
        'grapheme_strlen',
        'iconv_strlen',
        'mb_strlen',
        'sizeof',
        'strlen',
    ];

    /**
     * Decrements an integer by 1
     *
     * @param Node&Node\Scalar\LNumber $node
     *
     * @return Node\Scalar\LNumber
     */
    public function mutate(Node $node)
    {
        return new Node\Scalar\LNumber($node->value - 1);
    }

    protected function mutatesNode(Node $node): bool
    {
        if (!$node instanceof Node\Scalar\LNumber || $node->value === 1) {
            return false;
        }

        if ($this->isPartOfSizeComparison($node)) {
            return false;
        }

        return $this->isAllowedComparison($node);
    }

    private function isAllowedComparison(Node\Scalar\LNumber $node): bool
    {
        if ($node->value !== 0) {
            return true;
        }

        $parentNode = $node->getAttribute(ParentConnectorVisitor::PARENT_KEY);

        if (!$this->isComparison($parentNode)) {
            return true;
        }

        if ($parentNode->left instanceof Node\Expr\FuncCall && $parentNode->left->name instanceof Node\Name
            && in_array(
                $parentNode->left->name->toLowerString(),
                self::COUNT_NAMES,
                true)
        ) {
            return false;
        }

        if ($parentNode->right instanceof Node\Expr\FuncCall && $parentNode->right->name instanceof Node\Name
            && in_array(
                $parentNode->right->name->toLowerString(),
                self::COUNT_NAMES,
                true)
        ) {
            return false;
        }

        return true;
    }

    private function isComparison(Node $parentNode): bool
    {
        return $parentNode instanceof Node\Expr\BinaryOp\Identical
            || $parentNode instanceof Node\Expr\BinaryOp\NotIdentical
            || $parentNode instanceof Node\Expr\BinaryOp\Equal
            || $parentNode instanceof Node\Expr\BinaryOp\NotEqual
            || $parentNode instanceof Node\Expr\BinaryOp\Greater
            || $parentNode instanceof Node\Expr\BinaryOp\GreaterOrEqual
            || $parentNode instanceof Node\Expr\BinaryOp\Smaller
            || $parentNode instanceof Node\Expr\BinaryOp\SmallerOrEqual;
    }
}
