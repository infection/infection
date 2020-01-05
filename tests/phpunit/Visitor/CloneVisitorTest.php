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

namespace Infection\Tests\Visitor;

use Infection\Visitor\CloneVisitor;
use PhpParser\Node;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;

final class CloneVisitorTest extends BaseVisitorTest
{
    private const CODE = <<<'PHP'
<?php declare(strict_types=1);

namespace Acme;

function hello()
{
    return 'hello';
}
PHP;

    public function test_mutating_nodes_during_traverse_mutates_the_original_nodes(): void
    {
        $originalNodes = $this->parseCode(self::CODE);

        $originalDump = $this->dumpNodes($originalNodes);

        $traversedNodes = $this->traverse($originalNodes, [$this->createMutateStringValueVisitor()]);

        $newDump = $this->dumpNodes($traversedNodes);

        $this->assertSame($originalNodes, $traversedNodes, 'Expected the nodes to be identical');
        $this->assertNotSame($originalDump, $newDump, 'Expected the new nodes to have been mutated');
    }

    public function test_mutating_nodes_during_traverse_with_the_clone_visitor_does_not_mutate_the_original_nodes(): void
    {
        $originalNodes = $this->parseCode(self::CODE);

        $originalDump = $this->dumpNodes($originalNodes);

        $traversedNodes = $this->traverse(
            $originalNodes,
            [
                new CloneVisitor(),
                $this->createMutateStringValueVisitor(),
            ]
        );

        $newDump = $this->dumpNodes($traversedNodes);

        $this->assertNotSame($originalNodes, $traversedNodes, 'Expected the nodes to be identical');
        $this->assertNotSame($originalDump, $newDump, 'Expected the new nodes to have been mutated');
    }

    private function createMutateStringValueVisitor(): NodeVisitor
    {
        return new class() extends NodeVisitorAbstract {
            public function enterNode(Node $node)
            {
                if ($node instanceof Node\Scalar\String_) {
                    return new Node\Scalar\String_('foo');
                }

                return null;
            }
        };
    }
}
