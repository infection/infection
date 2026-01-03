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

namespace Infection\Ast\NodeVisitor;

use Infection\Ast\Metadata\Annotation;
use Infection\Ast\Metadata\NodeAnnotator;
use Infection\Ast\Metadata\TraverseContext;
use Infection\Source\Matcher\SourceLineMatcher;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Excludes nodes that do not belong to changed code from the git diff.
 */
final class ExcludeUnchangedNodesVisitor extends NodeVisitorAbstract
{
    public function __construct(
        private readonly TraverseContext $context,
        private readonly SourceLineMatcher $sourceLineMatcher,
    ) {
    }

    public function enterNode(Node $node): ?int
    {
        if (!$this->isPartOfTheDiff($node)) {
            NodeAnnotator::annotate($node, Annotation::NOT_PART_OF_THE_DIFF);

            return self::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
        }

        return null;
    }

    private function isPartOfTheDiff(Node $node): bool
    {
        return $this->sourceLineMatcher->touches(
            $this->context->sourceFilePathname,
            $node->getStartLine(),
            $node->getEndLine(),
        );
    }
}
