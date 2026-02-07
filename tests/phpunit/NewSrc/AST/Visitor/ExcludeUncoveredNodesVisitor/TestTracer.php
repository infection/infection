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

namespace Infection\Tests\NewSrc\AST\Visitor\ExcludeUncoveredNodesVisitor;

use function array_flip;
use function array_key_exists;
use function iter\any;
use newSrc\AST\Metadata\NodePosition;
use newSrc\TestFramework\Tracing\Tracer;
use PhpParser\Node;

final readonly class TestTracer implements Tracer
{
    /**
     * @var array<class-string<Node>, mixed>
     */
    private array $ignoredNodeClassNamesAsKeys;

    /**
     * @param list<class-string<Node>> $ignoredNodeClassNames
     * @param list<NodePosition> $coveredLines
     */
    public function __construct(
        array $ignoredNodeClassNames,
        private array $coveredLines = [],
    ) {
        $this->ignoredNodeClassNamesAsKeys = array_flip($ignoredNodeClassNames);
    }

    public function hasTests(
        string $sourceFilePathname,
        Node $node,
    ): bool {
        $nodePosition = NodePosition::create($node);

        if (array_key_exists($node::class, $this->ignoredNodeClassNamesAsKeys)) {
            return true;
        }

        return any(
            static fn (NodePosition $coveredLine) => self::isCovered(
                $coveredLine,
                $nodePosition,
            ),
            $this->coveredLines,
        );
    }

    private static function isCovered(
        NodePosition $coveredLine,
        NodePosition $nodePosition,
    ): bool {
        // 1.
        // 2.
        // 3.         covered
        // 4.   covered
        // 5.

        if (
            $coveredLine->startLine > $nodePosition->startLine
            || $coveredLine->endLine < $nodePosition->endLine
        ) {
            return false;
        }

        if (
            $coveredLine->startLine === $nodePosition->startLine
            && $coveredLine->startTokenPosition > $nodePosition->startTokenPosition
        ) {
            return false;
        }

        if (
            $coveredLine->endLine === $nodePosition->endLine
            && $coveredLine->endTokenPosition < $nodePosition->endTokenPosition
        ) {
            return false;
        }

        return true;
    }
}
