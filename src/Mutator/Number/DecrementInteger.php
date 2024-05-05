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
use Infection\Mutator\Definition;
use Infection\Mutator\GetMutatorName;
use Infection\Mutator\MutatorCategory;
use Infection\PhpParser\Visitor\ParentConnector;
use const PHP_INT_MAX;
use PhpParser\Node;

/**
 * @internal
 *
 * @extends AbstractNumberMutator<Node\Scalar\LNumber>
 */
final class DecrementInteger extends AbstractNumberMutator
{
    use GetMutatorName;

    private const COUNT_NAMES = [
        'count',
        'grapheme_strlen',
        'iconv_strlen',
        'mb_strlen',
        'sizeof',
        'strlen',
    ];

    public static function getDefinition(): Definition
    {
        return new Definition(
            'Decrements an integer value with 1.',
            MutatorCategory::ORTHOGONAL_REPLACEMENT,
            null,
            <<<'DIFF'
                - $a = 20;
                + $a = 19;
                DIFF,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @return iterable<Node\Scalar\LNumber>
     */
    public function mutate(Node $node): iterable
    {
        $parentNode = ParentConnector::getParent($node);

        $value = $node->value - 1;

        /*
         * Parser gives us only positive numbers we have to check if parent node
         * isn't a minus sign. If so, then means we have a negated positive number so
         * we have to add to it instead of substracting.
         */
        if ($parentNode instanceof Node\Expr\UnaryMinus) {
            // PHP Parser reads negative number as a pair of minus sign and a positive int,
            // but positive part of PHP_INT_MIN leads to an overflow into float. To work
            // around this we have to cast the result value back to int after adding one.
            $value = $node->value + 1;
        }

        yield new Node\Scalar\LNumber($value);
    }

    public function canMutate(Node $node): bool
    {
        if (!$node instanceof Node\Scalar\LNumber) {
            return false;
        }

        $parentNode = ParentConnector::getParent($node);

        // We cannot increment PHP_INT_MAX as part of a negative number, leads to parser bugs.
        if ($node->value === PHP_INT_MAX && $parentNode instanceof Node\Expr\UnaryMinus) {
            return false;
        }

        if (
            $node->value === 1
            && ($this->isPartOfComparison($node) || $parentNode instanceof Node\Expr\Assign)
        ) {
            return false;
        }

        if ($this->isArrayZeroIndexAccess($node)) {
            return false;
        }

        if ($this->isPartOfSizeComparison($node)) {
            return false;
        }

        if ($this->isPregSplitLimitZeroOrMinusOneArgument($node)) {
            return false;
        }

        return $this->isAllowedComparison($node);
    }

    private function isAllowedComparison(Node\Scalar\LNumber $node): bool
    {
        if ($node->value !== 0) {
            return true;
        }

        if (!$this->isPartOfComparison($node)) {
            return true;
        }

        $parentNode = ParentConnector::getParent($node);

        /** @var Node\Expr\BinaryOp $parentNode */
        if ($parentNode->left instanceof Node\Expr\FuncCall && $parentNode->left->name instanceof Node\Name
            && in_array(
                $parentNode->left->name->toLowerString(),
                self::COUNT_NAMES,
                true,
            )
        ) {
            return false;
        }

        if ($parentNode->right instanceof Node\Expr\FuncCall && $parentNode->right->name instanceof Node\Name
            && in_array(
                $parentNode->right->name->toLowerString(),
                self::COUNT_NAMES,
                true,
            )
        ) {
            return false;
        }

        return true;
    }

    private function isArrayZeroIndexAccess(Node\Scalar\LNumber $node): bool
    {
        if ($node->value !== 0) {
            return false;
        }

        if (ParentConnector::getParent($node) instanceof Node\Expr\ArrayDimFetch) {
            return true;
        }

        return false;
    }

    private function isPregSplitLimitZeroOrMinusOneArgument(Node\Scalar\LNumber $node): bool
    {
        if ($node->value !== 0) {
            return false;
        }

        $parentNode = ParentConnector::getParent($node);

        if (!$parentNode instanceof Node\Arg) {
            if (!$parentNode instanceof Node\Expr\UnaryMinus) {
                return false;
            }

            $parentNode = ParentConnector::getParent($node);

            if (!$parentNode instanceof Node\Arg) {
                return false;
            }
        }

        $parentNode = ParentConnector::getParent($parentNode);

        return $parentNode instanceof Node\Expr\FuncCall
            && $parentNode->name instanceof Node\Name
            && $parentNode->name->toLowerString() === 'preg_split'
        ;
    }
}
