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

use Infection\Mutator\Mutator;
use Infection\PhpParser\Visitor\ParentConnector;
use PhpParser\Node;

/**
 * @internal
 *
 * @template TNode of Node
 * @implements Mutator<TNode>
 */
abstract class AbstractNumberMutator implements Mutator
{
    protected function isPartOfSizeComparison(Node $node): bool
    {
        $parent = ParentConnector::findParent($node);

        return $this->isSizeComparison($parent);
    }

    protected function isPartOfComparison(Node $node): bool
    {
        $parent = ParentConnector::getParent($node);

        return $this->isComparison($parent);
    }

    private function isSizeComparison(?Node $node): bool
    {
        if ($node === null) {
            return false;
        }

        if ($node instanceof Node\Expr\UnaryMinus) {
            return $this->isSizeComparison(ParentConnector::findParent($node));
        }

        return $this->isSizeNode($node);
    }

    private function isSizeNode(Node $node): bool
    {
        return $node instanceof Node\Expr\BinaryOp\Greater
            || $node instanceof Node\Expr\BinaryOp\GreaterOrEqual
            || $node instanceof Node\Expr\BinaryOp\Smaller
            || $node instanceof Node\Expr\BinaryOp\SmallerOrEqual
        ;
    }

    private function isComparison(?Node $node): bool
    {
        if ($node === null) {
            return false;
        }

        if ($node instanceof Node\Expr\UnaryMinus) {
            return $this->isComparison(ParentConnector::findParent($node));
        }

        return $node instanceof Node\Expr\BinaryOp\Identical
            || $node instanceof Node\Expr\BinaryOp\NotIdentical
            || $node instanceof Node\Expr\BinaryOp\Equal
            || $node instanceof Node\Expr\BinaryOp\NotEqual
            || $this->isSizeNode($node)
        ;
    }
}
