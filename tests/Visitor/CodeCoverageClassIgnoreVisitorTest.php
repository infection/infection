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

use Infection\Visitor\CodeCoverageClassIgnoreVisitor;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
final class CodeCoverageClassIgnoreVisitorTest extends AbstractBaseVisitorTest
{
    private $spyVisitor;

    protected function setUp(): void
    {
        $this->spyVisitor = $this->getSpyVisitor();
    }

    public function test_it_do_not_travers_when_coverage_is_ignored(): void
    {
        $code = $this->getFileContent('Coverage/code-coverage-class-ignore.php');

        $this->parseAndTraverse($code);

        $this->assertFalse($this->spyVisitor->isNodeVisited(), 'ClassMethod node has been visited');
    }

    public function test_it_travers_nodes_when_coverage_is_not_ignored(): void
    {
        $code = $this->getFileContent('Coverage/code-coverage-class-not-ignored.php');

        $this->parseAndTraverse($code);

        $this->assertTrue($this->spyVisitor->isNodeVisited(), 'ClassMethod node has not been visited');
    }

    public function test_it_travers_nodes_when_coverage_is_not_ignored_but_has_a_comment(): void
    {
        $code = $this->getFileContent('Coverage/code-coverage-class-not-ignored-with-comment.php');

        $this->parseAndTraverse($code);

        $this->assertTrue($this->spyVisitor->isNodeVisited(), 'ClassMethod node has not been visited');
    }

    protected function parseAndTraverse(string $code): void
    {
        $nodes = $this->getNodes($code);

        $traverser = new NodeTraverser();

        $traverser->addVisitor(new CodeCoverageClassIgnoreVisitor());
        $traverser->addVisitor($this->spyVisitor);

        $traverser->traverse($nodes);
    }

    private function getSpyVisitor()
    {
        return new class() extends NodeVisitorAbstract {
            private $nodeVisited = false;

            public function leaveNode(Node $node): void
            {
                if ($node instanceof Node\Stmt\ClassMethod) {
                    $this->nodeVisited = true;
                }
            }

            public function isNodeVisited(): bool
            {
                return $this->nodeVisited;
            }
        };
    }
}
