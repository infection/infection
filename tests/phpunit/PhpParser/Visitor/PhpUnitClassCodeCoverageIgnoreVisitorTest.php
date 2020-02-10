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

namespace Infection\Tests\PhpParser\Visitor;

use Generator;
use Infection\PhpParser\Visitor\PhpUnitClassCodeCoverageIgnoreVisitor;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * @group integration Requires some I/O operations
 */
final class PhpUnitClassCodeCoverageIgnoreVisitorTest extends BaseVisitorTest
{
    private $spyVisitor;

    protected function setUp(): void
    {
        $this->spyVisitor = $this->createSpyVisitor();
    }

    public function test_it_does_not_traverse_a_class_which_has_the_code_coverage_ignore_PHPUnit_annotation(): void
    {
        $code = $this->getFileContent('Coverage/code-coverage-class-ignore.php');

        $this->parseAndTraverse($code);

        $this->assertFalse(
            $this->spyVisitor->isNodeVisited(),
            'ClassMethod node has been visited'
        );
    }

    /**
     * @dataProvider traversedCodeProvider
     */
    public function test_it_traverses_a_class_which_does_not_have_the_code_coverage_ignore_PHPUnit_annotation(
        string $path
    ): void {
        $code = $this->getFileContent($path);

        $this->parseAndTraverse($code);

        $this->assertTrue(
            $this->spyVisitor->isNodeVisited(),
            'ClassMethod node has not been visited'
        );
    }

    public function traversedCodeProvider(): Generator
    {
        yield ['Coverage/code-coverage-class-not-ignored.php'];

        yield ['Coverage/code-coverage-class-not-ignored-with-comment.php'];
    }

    private function parseAndTraverse(string $code): void
    {
        $nodes = $this->parseCode($code);

        $this->traverse(
            $nodes,
            [
                new PhpUnitClassCodeCoverageIgnoreVisitor(),
                $this->spyVisitor,
            ]
        );
    }

    private function createSpyVisitor()
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
