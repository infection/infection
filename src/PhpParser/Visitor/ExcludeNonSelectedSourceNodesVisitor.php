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

namespace Infection\PhpParser\Visitor;

use function array_pop;
use function array_reverse;
use Infection\Source\Matcher\SourceSymbolMatcher;
use Override;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
final class ExcludeNonSelectedSourceNodesVisitor extends NodeVisitorAbstract
{
    /**
     * @var list<Node\Stmt\ClassLike|Node\Stmt\ClassMethod>
     */
    private array $scopeStack = [];

    public function __construct(
        private readonly SourceSymbolMatcher $matcher,
    ) {
    }

    #[Override]
    public function beforeTraverse(array $nodes): null
    {
        $this->scopeStack = [];

        return null;
    }

    #[Override]
    public function enterNode(Node $node): null
    {
        if (
            $node instanceof Node\Stmt\ClassLike
            || $node instanceof Node\Stmt\ClassMethod
        ) {
            $this->scopeStack[] = $node;
        }

        if (
            LabelNodesAsEligibleVisitor::isEligible($node)
            && !$this->matches($node)
        ) {
            LabelNodesAsEligibleVisitor::markAsIneligible($node);
        }

        return null;
    }

    #[Override]
    public function leaveNode(Node $node): null
    {
        if (
            $node instanceof Node\Stmt\ClassLike
            || $node instanceof Node\Stmt\ClassMethod
        ) {
            array_pop($this->scopeStack);
        }

        return null;
    }

    private function matches(Node $node): bool
    {
        $method = null;

        foreach (array_reverse($this->scopeStack) as $scope) {
            if ($scope instanceof Node\Stmt\ClassMethod) {
                $method = $scope;

                continue;
            }

            // An anonymous class and its methods belong to the enclosing named symbol selection.
            if ($scope->name === null) {
                $method = null;

                continue;
            }

            return $this->matcher->matches($node, $scope, $method);
        }

        return false;
    }
}
