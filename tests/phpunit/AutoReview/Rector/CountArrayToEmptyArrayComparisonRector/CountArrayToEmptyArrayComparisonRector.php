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

namespace Infection\Tests\AutoReview\Rector\CountArrayToEmptyArrayComparisonRector;

use function count;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\If_;
use Rector\Rector\AbstractRector;

/**
 * Enforces the project's preference for explicit empty-array comparisons.
 * Only native array types qualify: Countable objects need their count() method.
 *
 * Adapted from Rector's original rule, with Infection specific argument checks.
 *
 * @see https://github.com/rectorphp/rector-src/blob/2f5b100964eb8986591b282c852c8e1a1d5233e4/rules/CodingStyle/Rector/FuncCall/CountArrayToEmptyArrayComparisonRector.php
 */
final class CountArrayToEmptyArrayComparisonRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    #[Override]
    public function getNodeTypes(): array
    {
        return [
            Identical::class,
            NotIdentical::class,
            Greater::class,
            Smaller::class,
            BooleanNot::class,
            If_::class,
            ElseIf_::class,
        ];
    }

    #[Override]
    public function refactor(Node $node): ?Node
    {
        return match (true) {
            $node instanceof If_,
            $node instanceof ElseIf_ => $this->refactorCondition($node),
            $node instanceof BooleanNot => $this->refactorNegatedCount($node),
            $node instanceof Identical,
            $node instanceof NotIdentical => $this->refactorEqualityComparison($node),
            $node instanceof Greater,
            $node instanceof Smaller => $this->refactorPositiveCountComparison($node),
            default => null,
        };
    }

    private function refactorCondition(If_|ElseIf_ $node): If_|ElseIf_|null
    {
        $array = $this->matchCountedArray($node->cond);

        if ($array === null) {
            return null;
        }

        $node->cond = new NotIdentical(
            $array,
            new Array_(),
        );

        return $node;
    }

    private function refactorNegatedCount(BooleanNot $node): ?Identical
    {
        $array = $this->matchCountedArray($node->expr);

        if ($array === null) {
            return null;
        }

        return new Identical(
            $array,
            new Array_(),
        );
    }

    private function refactorEqualityComparison(Identical|NotIdentical $node): Identical|NotIdentical|null
    {
        $array = match (true) {
            self::isZero($node->right) => $this->matchCountedArray($node->left),
            self::isZero($node->left) => $this->matchCountedArray($node->right),
            default => null,
        };

        if ($array === null) {
            return null;
        }

        return match (true) {
            $node instanceof Identical => new Identical(
                $array,
                new Array_(),
            ),
            default => new NotIdentical(
                $array,
                new Array_(),
            ),
        };
    }

    private function refactorPositiveCountComparison(Greater|Smaller $node): ?NotIdentical
    {
        [
            $countExpression,
            $lowerBound,
        ] = match (true) {
            $node instanceof Greater => [
                $node->left,
                $node->right,
            ],
            default => [
                $node->right,
                $node->left,
            ],
        };

        if (!self::isZero($lowerBound)) {
            return null;
        }

        $array = $this->matchCountedArray($countExpression);

        if ($array === null) {
            return null;
        }

        return new NotIdentical(
            $array,
            new Array_(),
        );
    }

    private function matchCountedArray(Expr $expr): ?Expr
    {
        if (!$expr instanceof FuncCall) {
            return null;
        }

        if (!$this->isName($expr, 'count')) {
            return null;
        }

        if ($expr->isFirstClassCallable()) {
            return null;
        }

        $array = self::matchCountArgument($expr);

        if ($array === null) {
            return null;
        }

        $type = $this->getNativeType($array);

        if (!$type->isArray()->yes()) {
            return null;
        }

        return $array;
    }

    private static function matchCountArgument(FuncCall $countCall): ?Expr
    {
        $args = $countCall->getArgs();

        // Keep explicit modes: removing them could hide errors or side effects.
        if (count($args) !== 1) {
            return null;
        }

        $arg = $args[0];

        if ($arg->unpack) {
            return null;
        }

        if ($arg->name !== null && $arg->name->toString() !== 'value') {
            return null;
        }

        return $arg->value;
    }

    private static function isZero(Expr $expr): bool
    {
        return $expr instanceof Int_ && $expr->value === 0;
    }
}
