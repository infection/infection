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

use Infection\Mutator\Definition;
use Infection\Mutator\GetMutatorName;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorCategory;
use Infection\PhpParser\Visitor\ParentConnector;
use PhpParser\Node;
use Webmozart\Assert\Assert;

/**
 * @internal
 *
 * @implements Mutator<Node>
 */
final class InstanceOf_ implements Mutator
{
    use GetMutatorName;

    public static function getDefinition(): Definition
    {
        return new Definition(
            'Replaces an `instanceof` comparison with its negated counterpart.',
            MutatorCategory::ORTHOGONAL_REPLACEMENT,
            null,
            <<<'DIFF'
                - $a = $b instanceof User;
                # Mutation 1
                + $a = !$b instanceof User;
                DIFF,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @return iterable<Node\Expr>
     */
    public function mutate(Node $node): iterable
    {
        if ($node instanceof Node\Expr\BooleanNot) {
            yield $node->expr;

            return;
        }

        Assert::isInstanceOf($node, Node\Expr\Instanceof_::class);

        $parentNode = ParentConnector::findParent($node);

        if ($parentNode instanceof Node\Arg) {
            yield new Node\Expr\ConstFetch(new Node\Name('true'));

            yield new Node\Expr\ConstFetch(new Node\Name('false'));

            return;
        }

        yield new Node\Expr\BooleanNot($node);
    }

    public function canMutate(Node $node): bool
    {
        if ($node instanceof Node\Expr\BooleanNot && $node->expr instanceof Node\Expr\Instanceof_) {
            return true;
        }

        if (!$node instanceof Node\Expr\Instanceof_) {
            return false;
        }

        if ($this->isArgumentOfAssertFunction($node)) {
            return false;
        }

        // prevent double negation, e.g. "!! $example instanceof Example"
        if (ParentConnector::findParent($node) instanceof Node\Expr\BooleanNot) {
            return false;
        }

        return true;
    }

    private function isArgumentOfAssertFunction(Node\Expr\Instanceof_ $node): bool
    {
        $parentNode = ParentConnector::findParent($node);
        $grandParentNode = $parentNode !== null ? ParentConnector::findParent($parentNode) : null;

        if (!$grandParentNode instanceof Node\Expr\FuncCall || !$grandParentNode->name instanceof Node\Name) {
            return false;
        }

        return $grandParentNode->name->toLowerString() === 'assert';
    }
}
