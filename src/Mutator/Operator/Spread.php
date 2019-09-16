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

namespace Infection\Mutator\Operator;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayItem;

/**
 * @internal
 */
final class Spread extends Mutator
{
    /**
     * Replaces "[...[1, 2, 3], 4];" with "[array_slice([1, 2, 3], 0, 1)[0], 4]"
     *
     * @param ArrayItem $node
     *
     * @return ArrayItem
     */
    public function mutate(Node $node)
    {
        $node->unpack = false;

        if ($node->value instanceof Node\Expr\Array_) {
            $slicedArray = $this->getFirstElement($node->value);
        } else {
            $isArray = new Node\Expr\FuncCall(new Node\Name('is_array'), [new Node\Arg($node->value)]);
            $resultArray = new Node\Expr\Ternary(
                $isArray,
                $node->value,
                new Node\Expr\FuncCall(new Node\Name('iterator_to_array'), [new Node\Arg($node->value)])
            );

            $slicedArray = $this->getFirstElement($resultArray);
        }

        $newValue = new Node\Expr\ArrayDimFetch(
            $slicedArray,
            new Node\Scalar\LNumber(0),
            $node->value->getAttributes()
        );

        $node->value = $newValue;

        return $node;
    }

    protected function mutatesNode(Node $node): bool
    {
        return $node instanceof Node\Expr\ArrayItem
            && $node->unpack
            && $this->isSupportedValueType($node->value);
    }

    private function isSupportedValueType(Node\Expr $value): bool
    {
        return $value instanceof Node\Expr\Array_
            || $value instanceof Node\Expr\Variable
            || $value instanceof Node\Expr\MethodCall
            || $value instanceof Node\Expr\FuncCall;
    }

    private function getFirstElement(Node\Expr $resultArray): Node\Expr\FuncCall
    {
        return new Node\Expr\FuncCall(new Node\Name('array_slice'), [
            new Node\Arg($resultArray),
            new Node\Arg(new Node\Scalar\LNumber(0)),
            new Node\Arg(new Node\Scalar\LNumber(1)),
        ]);
    }
}
