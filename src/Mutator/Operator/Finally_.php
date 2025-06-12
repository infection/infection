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

use function count;
use Infection\Mutator\Definition;
use Infection\Mutator\GetMutatorName;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorCategory;
use Infection\PhpParser\Visitor\ParentConnector;
use PhpParser\Node;
use PhpParser\NodeVisitor;
use Webmozart\Assert\Assert;

/**
 * @internal
 *
 * @implements Mutator<Node\Stmt\Finally_>
 */
final class Finally_ implements Mutator
{
    use GetMutatorName;

    public static function getDefinition(): Definition
    {
        return new Definition(
            'Removes the `finally` block.',
            MutatorCategory::SEMANTIC_REDUCTION,
            null,
            <<<'DIFF'
                try {
                    // do smth
                + }
                - } finally {
                -
                - }
                DIFF,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @return iterable<int|Node\Stmt\Nop>
     */
    public function mutate(Node $node): iterable
    {
        yield NodeVisitor::REPLACE_WITH_NULL;
    }

    public function canMutate(Node $node): bool
    {
        if (!$node instanceof Node\Stmt\Finally_) {
            return false;
        }

        return $this->hasAtLeastOneCatchBlock($node);
    }

    private function hasAtLeastOneCatchBlock(Node $node): bool
    {
        /** @var Node\Stmt\TryCatch $parentNode */
        $parentNode = ParentConnector::getParent($node);
        Assert::isInstanceOf($parentNode, Node\Stmt\TryCatch::class);

        return count($parentNode->catches) > 0;
    }
}
