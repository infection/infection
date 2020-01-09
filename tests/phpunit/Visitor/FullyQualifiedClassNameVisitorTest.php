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

use function array_map;
use Generator;
use Infection\Visitor\FullyQualifiedClassNameVisitor;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * @group integration Requires I/O reads
 */
final class FullyQualifiedClassNameVisitorTest extends BaseVisitorTest
{
    private $spyVisitor;

    protected function setUp(): void
    {
        $this->spyVisitor = $this->getSpyVisitor();
    }

    /**
     * @dataProvider codeProvider
     *
     * @param string[] $expectedProcessedNodesFqcn
     */
    public function test_it_adds_FQCN_to_the_appropriate_node(
        string $path,
        array $expectedProcessedNodesFqcn
    ): void {
        $code = $this->getFileContent($path);

        $this->parseAndTraverse($code);

        $actualProcessedNodesFqcn = array_map(
            static function (Node $node): string {
                return $node->getAttribute(FullyQualifiedClassNameVisitor::FQN_KEY)->toString();
            },
            $this->spyVisitor->processedNodes
        );

        $this->assertSame($expectedProcessedNodesFqcn, $actualProcessedNodesFqcn);
    }

    public function codeProvider(): Generator
    {
        yield [
            'Fqcn/fqcn-empty-class.php',
            ['FqcnEmptyClass\EmptyClass'],
        ];

        yield [
            'Fqcn/fqcn-class-interface.php',
            ['FqcnClassInterface\Ci'],
        ];

        yield [
            'Fqcn/fqcn-anonymous-class.php',
            ['FqcnClassAnonymous\Ci'],
        ];
    }

    private function getSpyVisitor()
    {
        return new class() extends NodeVisitorAbstract {
            public $processedNodes = [];

            public function enterNode(Node $node): void
            {
                if ($node->getAttribute(FullyQualifiedClassNameVisitor::FQN_KEY)) {
                    $this->processedNodes[] = $node;
                }
            }
        };
    }

    private function parseAndTraverse($code): void
    {
        $nodes = $this->parseCode($code);

        $this->traverse(
            $nodes,
            [
                new FullyQualifiedClassNameVisitor(),
                $this->spyVisitor,
            ]
        );
    }
}
