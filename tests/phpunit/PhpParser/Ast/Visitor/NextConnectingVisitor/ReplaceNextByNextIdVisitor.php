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

namespace Infection\Tests\PhpParser\Ast\Visitor\NextConnectingVisitor;

use Infection\PhpParser\Visitor\NextConnectingVisitor;
use Infection\Tests\PhpParser\Ast\Visitor\AddIdToTraversedNodesVisitor\AddIdToTraversedNodesVisitor;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * The goal of this visitor is to make it easier to text 'next' as the latter cannot
 * be reliably printed.
 */
final class ReplaceNextByNextIdVisitor extends NodeVisitorAbstract
{
    public const NEXT_NODE_ID_ATTRIBUTE = 'nextNodeId';

    public function enterNode(Node $node): void
    {
        $this->replaceNextByNodeId($node);
    }

    private function replaceNextByNodeId(Node $node): void
    {
        $next = $node->getAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE);

        if ($next === null) {
            return;
        }

        $nodeAttributes = $node->getAttributes();
        unset($nodeAttributes[NextConnectingVisitor::NEXT_ATTRIBUTE]);
        $nodeAttributes[self::NEXT_NODE_ID_ATTRIBUTE] = AddIdToTraversedNodesVisitor::getNodeId($next);

        $node->setAttributes($nodeAttributes);
    }
}
