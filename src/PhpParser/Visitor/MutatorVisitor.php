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

use function array_key_exists;
use Infection\Mutation\Mutation;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
final class MutatorVisitor extends NodeVisitorAbstract
{
    public function __construct(private readonly Mutation $mutation)
    {
    }

    public function leaveNode(Node $node)
    {
        $attributes = $node->getAttributes();

        if (!array_key_exists('startTokenPos', $attributes)) {
            return null;
        }

        $mutatedAttributes = $this->mutation->getAttributes();

        $samePosition = $attributes['startTokenPos'] === $mutatedAttributes['startTokenPos']
            && $attributes['endTokenPos'] === $mutatedAttributes['endTokenPos'];

        if ($samePosition && $this->mutation->getMutatedNodeClass() === $node::class) {
            return $this->mutation->getMutatedNode()->unwrap();
            // TODO STOP TRAVERSING
            // TODO check all built-in visitors, in particular FirstFindingVisitor
            // TODO beforeTraverse - FirstFindingVisitor
            // TODO enterNode instead of leaveNode for '<' mutation to not travers children?
        }

        return null;
    }
}
