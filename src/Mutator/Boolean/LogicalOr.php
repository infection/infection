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

use function array_intersect;
use function in_array;
use Infection\Mutator\Definition;
use Infection\Mutator\GetMutatorName;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorCategory;
use Infection\Mutator\Util\NameResolver;
use function is_string;
use LogicException;
use PhpParser\Node;
use ReflectionClass;
use ReflectionException;

/**
 * @internal
 *
 * @implements Mutator<Node\Expr\BinaryOp\BooleanOr>
 */
final class LogicalOr implements Mutator
{
    use GetMutatorName;

    private ?string $seenVariabeName = null;

    public static function getDefinition(): Definition
    {
        return new Definition(
            'Replaces an OR operator (`||`) with an AND operator (`&&`).',
            MutatorCategory::ORTHOGONAL_REPLACEMENT,
            null,
            <<<'DIFF'
                - $a = $b || $c;
                + $a = $b && $c;
                DIFF,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * Replaces "||" with "&&"
     *
     * @return iterable<Node\Expr\BinaryOp\BooleanAnd>
     */
    public function mutate(Node $node): iterable
    {
        yield new Node\Expr\BinaryOp\BooleanAnd($node->left, $node->right, $node->getAttributes());
    }

    public function canMutate(Node $node): bool
    {
        if (!$node instanceof Node\Expr\BinaryOp\BooleanOr) {
            return false;
        }

        $nodeLeft = $node->left;
        $nodeRight = $node->right;

        if (
            $nodeLeft instanceof Node\Expr\Instanceof_
            && $nodeRight instanceof Node\Expr\Instanceof_
        ) {
            return $this->isInstanceOfConditionMutable($nodeLeft) || $this->isInstanceOfConditionMutable($nodeRight);
        }

        if (
            !$nodeLeft instanceof Node\Expr\BinaryOp
            || !$nodeRight instanceof Node\Expr\BinaryOp
        ) {
            return true;
        }

        $leftSigil = $nodeLeft->getOperatorSigil();
        $rightSigil = $nodeRight->getOperatorSigil();

        $nodeLeftLeft = $nodeLeft->left;
        $nodeLeftRight = $nodeLeft->right;

        $nodeRightLeft = $nodeRight->left;
        $nodeRightRight = $nodeRight->right;

        if ($leftSigil === '===' && $rightSigil === '===') {
            $varNameLeft = [];

            if ($nodeLeftLeft instanceof Node\Expr\Variable) {
                $varNameLeft[] = $nodeLeftLeft->name;
            }

            if ($nodeLeftRight instanceof Node\Expr\Variable) {
                $varNameLeft[] = $nodeLeftRight->name;
            }

            $varNameRight = [];

            if ($nodeRightLeft instanceof Node\Expr\Variable) {
                $varNameRight[] = $nodeRightLeft->name;
            }

            if ($nodeRightRight instanceof Node\Expr\Variable) {
                $varNameRight[] = $nodeRightRight->name;
            }

            return array_intersect($varNameLeft, $varNameRight) === [];
        }

        $compareSigils = ['>', '<', '>=', '<='];

        if (
            in_array($leftSigil, $compareSigils, true)
            && in_array($rightSigil, $compareSigils, true)
        ) {
            $varNameLeft = null;
            $valueLeft = null;

            if ($nodeLeftLeft instanceof Node\Expr\Variable) {
                $varNameLeft = $nodeLeftLeft->name;
            } elseif (
                $nodeLeftLeft instanceof Node\Scalar\LNumber
                || $nodeLeftLeft instanceof Node\Scalar\DNumber
            ) {
                $valueLeft = $nodeLeftLeft->value;
            } else {
                return true;
            }

            if ($nodeLeftRight instanceof Node\Expr\Variable && $varNameLeft === null) {
                $varNameLeft = $nodeLeftRight->name;
            } elseif (
                (
                    $nodeLeftRight instanceof Node\Scalar\LNumber
                    || $nodeLeftRight instanceof Node\Scalar\DNumber
                ) && $valueLeft === null
            ) {
                $valueLeft = $nodeLeftRight->value;
            } else {
                return true;
            }

            $varNameRight = null;
            $valueRight = null;

            if ($nodeRightLeft instanceof Node\Expr\Variable) {
                $varNameRight = $nodeRightLeft->name;
            } elseif (
                $nodeRightLeft instanceof Node\Scalar\LNumber
                || $nodeRightLeft instanceof Node\Scalar\DNumber
            ) {
                $valueRight = $nodeRightLeft->value;
            } else {
                return true;
            }

            if ($nodeRightRight instanceof Node\Expr\Variable && $varNameRight === null) {
                $varNameRight = $nodeRightRight->name;
            } elseif (
                (
                    $nodeRightRight instanceof Node\Scalar\LNumber
                    || $nodeRightRight instanceof Node\Scalar\DNumber
                ) && $valueRight === null
            ) {
                $valueRight = $nodeRightRight->value;
            } else {
                return true;
            }

            if ($varNameLeft !== $varNameRight) {
                return true;
            }

            return (match ("{$leftSigil}::{$rightSigil}") {
                '<::>' => static fn () => $valueRight < $valueLeft, // a<5 && a>7; 7<a<5; 7<5;
                '<::>=' => static fn () => $valueRight < $valueLeft, // a<5 && a>=7; 7<=a<5; 7<5;
                '<=::>=' => static fn () => $valueRight <= $valueLeft, // a<=5 && a>=7; 7<=a<=5; 7<=5;
                '<=::>' => static fn () => $valueRight < $valueLeft, // a<=5 && a>7; 7<a<=5; 7<5;
                '>::<' => static fn () => $valueRight > $valueLeft, // a>5 && a<7; 7>a>5; 7>5;
                '>::<=' => static fn () => $valueRight > $valueLeft, // a>5 && a<=7; 7>=a>5; 7>5;
                '>=::<=' => static fn () => $valueRight >= $valueLeft, // a>=5 && a<=7; 7>=a>=5; 7>=5;
                '>=::<' => static fn () => $valueRight > $valueLeft, // a>=5 && a<7; 7>a>=5; 7>5;
                default => throw new LogicException('This is an unreachable statement.'),
            })();
        }

        return true;
    }

    private function isInstanceOfConditionMutable(Node\Expr\Instanceof_ $node): bool
    {
        if (
            $node->expr instanceof Node\Expr\Variable
            && is_string($node->expr->name)
            && $node->class instanceof Node\Name
        ) {
            if ($this->seenVariabeName === null) {
                $this->seenVariabeName = $node->expr->name;
            } else {
                if ($this->seenVariabeName !== $node->expr->name) {
                    return true;
                }
            }

            $resolvedName = NameResolver::resolveName($node->class);

            try {
                $reflectionClass = new ReflectionClass($resolvedName->name); // @phpstan-ignore argument.type

                if (!$reflectionClass->isInterface() && !$reflectionClass->isTrait()) {
                    return false;
                }
            } catch (ReflectionException) {
            }
        }

        return true;
    }
}
