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

use Infection\Mutator\Definition;
use Infection\Mutator\GetMutatorName;
use Infection\Mutator\MutatorCategory;
use Infection\PhpParser\Visitor\ParentConnector;
use PhpParser\Node;

/**
 * @internal
 */
final class OneZeroInteger extends AbstractNumberMutator
{
    use GetMutatorName;

    public static function getDefinition(): ?Definition
    {
        return new Definition(
            <<<'TXT'
Replaces a zero integer value (`0`) with a non-zero integer value (`1`) and vice-versa.
TXT
            ,
            MutatorCategory::ORTHOGONAL_REPLACEMENT,
            null
        );
    }

    /**
     * @param Node\Scalar\LNumber $node
     *
     * @return iterable<Node\Scalar\LNumber>
     */
    public function mutate(Node $node): iterable
    {
        if ($node->value === 0) {
            yield new Node\Scalar\LNumber(1);

            return;
        }

        yield new Node\Scalar\LNumber(0);
    }

    public function canMutate(Node $node): bool
    {
        return $node instanceof Node\Scalar\LNumber
            && ($node->value === 0 || $node->value === 1)
            && !$this->isPartOfSizeComparison($node)
            && !$this->isPregSplitLimitMinusOneValue($node);
    }

    /**
     * @param Node\Scalar\LNumber $node
     *
     * @return bool
     */
    private function isPregSplitLimitMinusOneValue(Node $node): bool
    {
        $minusNode = ParentConnector::getParent($node);

        if (!$minusNode instanceof Node\Expr\UnaryMinus) {
            return false;
        }

        $argNode = ParentConnector::getParent($minusNode);

        if (!$argNode instanceof Node\Arg) {
            return false;
        }

        $funcNode = ParentConnector::getParent($argNode);

        if (
            $funcNode instanceof Node\Expr\FuncCall &&
            $funcNode->name instanceof Node\Name &&
            $funcNode->name->toLowerString() === 'preg_split' &&
            count($funcNode->args) >= 3 &&
            spl_object_id($funcNode->args[2]) === spl_object_id($argNode)
        ) {
            return true;
        }

        return false;
    }
}
