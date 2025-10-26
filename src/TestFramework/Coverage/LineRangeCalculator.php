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

namespace Infection\TestFramework\Coverage;

use Infection\PhpParser\Visitor\ParentConnector;
use Infection\PhpParser\Visitor\ReflectionVisitor;
use PhpParser\Node;

/**
 * @internal
 */
final class LineRangeCalculator
{
    public function calculateRange(Node $originalNode): NodeLineRangeData
    {
        if ($originalNode->getAttribute(ReflectionVisitor::IS_ON_FUNCTION_SIGNATURE, false) === true) {
            $startLine = $originalNode->getStartLine();

            // function signature node should always be 1-line range: (start, start)
            return new NodeLineRangeData($startLine, $startLine);
        }

        $outerMostArrayNode = $this->getOuterMostArrayNode($originalNode);

        return new NodeLineRangeData($outerMostArrayNode->getStartLine(), $outerMostArrayNode->getEndLine());
    }

    /**
     * If the node is part of an array, this will find the outermost array.
     * Otherwise this will return the node itself
     */
    private function getOuterMostArrayNode(Node $node): Node
    {
        $outerMostArrayParent = $node;

        do {
            if ($node instanceof Node\Expr\Array_) {
                $outerMostArrayParent = $node;
            }

            $node = ParentConnector::findParent($node);
        } while ($node !== null);

        return $outerMostArrayParent;
    }
}
