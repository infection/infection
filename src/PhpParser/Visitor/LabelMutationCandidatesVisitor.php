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

use Infection\Source\Matcher\SourceLineMatcher;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * This mutator aims at reflecting the behaviour of NodeMutationGenerator to
 * give a visual of the nodes it is look at.
 *
 * This is different from "visited" and "eligible". Indeed, at the time of
 * writing a node may be:
 * - Visited without being eligible or a mutation candidate (e.g. a namespace statement).
 * - Eligible without being a mutation candidate (as NodeMutationGenerator
 *   currently has additional checks on top of the eligibility).
 *
 * @see NodeMutationGenerator
 *
 * @internal
 */
final class LabelMutationCandidatesVisitor extends NodeVisitorAbstract
{
    public const MUTATION_CANDIDATE = 'mutationCandidate';

    public function __construct(
        private readonly string $filePath,
        private readonly SourceLineMatcher $sourceLineMatcher,
    ) {
    }

    public function enterNode(Node $node): null
    {
        MarkTraversedNodesAsVisitedVisitor::markAsVisited($node);

        if (!LabelNodesAsEligibleVisitor::isEligible($node)) {
            return null;
        }

        if (!$this->isOnFunctionSignature($node)
            && !$this->isInsideFunction($node)
        ) {
            return null;
        }

        /** @psalm-suppress InvalidArgument */
        if (!$this->sourceLineMatcher->touches($this->filePath, $node->getStartLine(), $node->getEndLine())) {
            return null;
        }

        self::markAsAMutationCandidate($node);

        return null;
    }

    public static function markAsAMutationCandidate(Node $node): void
    {
        $node->setAttribute(self::MUTATION_CANDIDATE, true);
    }

    public static function isAMutationCandidate(Node $node): bool
    {
        return $node->hasAttribute(self::MUTATION_CANDIDATE);
    }

    private function isOnFunctionSignature(Node $node): bool
    {
        return $node->getAttribute(ReflectionVisitor::IS_ON_FUNCTION_SIGNATURE, false);
    }

    private function isInsideFunction(Node $node): bool
    {
        return $node->getAttribute(ReflectionVisitor::IS_INSIDE_FUNCTION_KEY, false);
    }
}
