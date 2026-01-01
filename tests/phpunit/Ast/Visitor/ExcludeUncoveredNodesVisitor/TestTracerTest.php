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

namespace Infection\Tests\Ast\Visitor\ExcludeUncoveredNodesVisitor;

use;
use newSrc\AST\Metadata\NodePosition;
use PhpParser\Node;
use PhpParser\Node\Name;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TestTracer::class)]
final class TestTracerTest extends TestCase
{
    /**
     * @param list<class-string<Node>> $ignoredNodeClassNames
     * @param list<NodePosition> $coveredLines
     */
    #[DataProvider('nodeProvider')]
    public function test_it_can_tell_when_a_node_has_tests(
        array $ignoredNodeClassNames,
        array $coveredLines,
        NodePosition $nodePosition,
        bool $expected,
    ): void {
        $tracer = new TestTracer(
            $ignoredNodeClassNames,
            $coveredLines,
        );

        $result = $tracer->hasTests(
            '/path/to/file.php',
            self::createNode($nodePosition),
        );

        $this->assertSame($expected, $result);
    }

    public static function nodeProvider(): iterable
    {
        yield 'no covered lines' => [
            [],
            [],
            new NodePosition(
                startLine: 5,
                startTokenPosition: 5,
                endLine: 5,
                endTokenPosition: 20,
            ),
            false,
        ];

        yield 'single line exact match' => [
            [],
            [
                new NodePosition(
                    startLine: 5,
                    startTokenPosition: 5,
                    endLine: 5,
                    endTokenPosition: 20,
                ),
            ],
            new NodePosition(
                startLine: 5,
                startTokenPosition: 5,
                endLine: 5,
                endTokenPosition: 20,
            ),
            true,
        ];

        yield 'single line, covered line is the line before' => [
            [],
            [
                new NodePosition(
                    startLine: 4,
                    startTokenPosition: 5,
                    endLine: 4,
                    endTokenPosition: 20,
                ),
            ],
            new NodePosition(
                startLine: 5,
                startTokenPosition: 5,
                endLine: 5,
                endTokenPosition: 20,
            ),
            false,
        ];

        yield 'single line, covered line is the line after' => [
            [],
            [
                new NodePosition(
                    startLine: 6,
                    startTokenPosition: 5,
                    endLine: 6,
                    endTokenPosition: 20,
                ),
            ],
            new NodePosition(
                startLine: 5,
                startTokenPosition: 5,
                endLine: 5,
                endTokenPosition: 20,
            ),
            false,
        ];

        yield 'single line, covered line starts before node' => [
            [],
            [
                new NodePosition(
                    startLine: 5,
                    startTokenPosition: 4,
                    endLine: 5,
                    endTokenPosition: 20,
                ),
            ],
            new NodePosition(
                startLine: 5,
                startTokenPosition: 5,
                endLine: 5,
                endTokenPosition: 20,
            ),
            true,
        ];

        yield 'single line, covered line starts after node' => [
            [],
            [
                new NodePosition(
                    startLine: 5,
                    startTokenPosition: 6,
                    endLine: 5,
                    endTokenPosition: 20,
                ),
            ],
            new NodePosition(
                startLine: 5,
                startTokenPosition: 5,
                endLine: 5,
                endTokenPosition: 20,
            ),
            false,
        ];

        yield 'single line, covered line ends before node' => [
            [],
            [
                new NodePosition(
                    startLine: 5,
                    startTokenPosition: 5,
                    endLine: 5,
                    endTokenPosition: 19,
                ),
            ],
            new NodePosition(
                startLine: 5,
                startTokenPosition: 5,
                endLine: 5,
                endTokenPosition: 20,
            ),
            false,
        ];

        yield 'single line, covered line ends after node' => [
            [],
            [
                new NodePosition(
                    startLine: 5,
                    startTokenPosition: 5,
                    endLine: 5,
                    endTokenPosition: 21,
                ),
            ],
            new NodePosition(
                startLine: 5,
                startTokenPosition: 5,
                endLine: 5,
                endTokenPosition: 20,
            ),
            true,
        ];

        yield 'multi-line exact match' => [
            [],
            [
                new NodePosition(
                    startLine: 3,
                    startTokenPosition: 10,
                    endLine: 7,
                    endTokenPosition: 20,
                ),
            ],
            new NodePosition(
                startLine: 3,
                startTokenPosition: 10,
                endLine: 7,
                endTokenPosition: 20,
            ),
            true,
        ];

        yield 'multi-line, covered lines start before node' => [
            [],
            [
                new NodePosition(
                    startLine: 2,
                    startTokenPosition: 10,
                    endLine: 7,
                    endTokenPosition: 20,
                ),
            ],
            new NodePosition(
                startLine: 3,
                startTokenPosition: 10,
                endLine: 7,
                endTokenPosition: 20,
            ),
            true,
        ];

        yield 'multi-line, covered lines start after node' => [
            [],
            [
                new NodePosition(
                    startLine: 4,
                    startTokenPosition: 10,
                    endLine: 7,
                    endTokenPosition: 20,
                ),
            ],
            new NodePosition(
                startLine: 3,
                startTokenPosition: 10,
                endLine: 7,
                endTokenPosition: 20,
            ),
            false,
        ];

        yield 'multi-line, covered lines ends before node' => [
            [],
            [
                new NodePosition(
                    startLine: 3,
                    startTokenPosition: 10,
                    endLine: 6,
                    endTokenPosition: 20,
                ),
            ],
            new NodePosition(
                startLine: 3,
                startTokenPosition: 10,
                endLine: 7,
                endTokenPosition: 20,
            ),
            false,
        ];

        yield 'multi-line, covered lines ends after node' => [
            [],
            [
                new NodePosition(
                    startLine: 3,
                    startTokenPosition: 10,
                    endLine: 8,
                    endTokenPosition: 20,
                ),
            ],
            new NodePosition(
                startLine: 3,
                startTokenPosition: 10,
                endLine: 7,
                endTokenPosition: 20,
            ),
            true,
        ];

        yield 'multi-line, covered lines starts at the same line before node' => [
            [],
            [
                new NodePosition(
                    startLine: 3,
                    startTokenPosition: 9,
                    endLine: 8,
                    endTokenPosition: 20,
                ),
            ],
            new NodePosition(
                startLine: 3,
                startTokenPosition: 10,
                endLine: 7,
                endTokenPosition: 20,
            ),
            true,
        ];

        yield 'multi-line, covered lines starts at the same line after node' => [
            [],
            [
                new NodePosition(
                    startLine: 3,
                    startTokenPosition: 11,
                    endLine: 8,
                    endTokenPosition: 20,
                ),
            ],
            new NodePosition(
                startLine: 3,
                startTokenPosition: 10,
                endLine: 7,
                endTokenPosition: 20,
            ),
            false,
        ];

        yield 'multi-line, covered lines ends at the same line before node' => [
            [],
            [
                new NodePosition(
                    startLine: 3,
                    startTokenPosition: 10,
                    endLine: 7,
                    endTokenPosition: 19,
                ),
            ],
            new NodePosition(
                startLine: 3,
                startTokenPosition: 10,
                endLine: 7,
                endTokenPosition: 20,
            ),
            false,
        ];

        yield 'multi-line, covered lines ends at the same line after node' => [
            [],
            [
                new NodePosition(
                    startLine: 3,
                    startTokenPosition: 10,
                    endLine: 7,
                    endTokenPosition: 21,
                ),
            ],
            new NodePosition(
                startLine: 3,
                startTokenPosition: 10,
                endLine: 7,
                endTokenPosition: 20,
            ),
            true,
        ];

        yield 'multiple covered lines cover node' => [
            [],
            [
                new NodePosition(
                    startLine: 3,
                    startTokenPosition: 10,
                    endLine: 7,
                    endTokenPosition: 20,
                ),
                new NodePosition(
                    startLine: 2,
                    startTokenPosition: 10,
                    endLine: 8,
                    endTokenPosition: 20,
                ),
            ],
            new NodePosition(
                startLine: 3,
                startTokenPosition: 10,
                endLine: 7,
                endTokenPosition: 20,
            ),
            true,
        ];

        yield 'multiple covered lines none cover node' => [
            [],
            [
                new NodePosition(
                    startLine: 1,
                    startTokenPosition: 10,
                    endLine: 2,
                    endTokenPosition: 20,
                ),
                new NodePosition(
                    startLine: 7,
                    startTokenPosition: 10,
                    endLine: 8,
                    endTokenPosition: 20,
                ),
            ],
            new NodePosition(
                startLine: 3,
                startTokenPosition: 10,
                endLine: 7,
                endTokenPosition: 20,
            ),
            false,
        ];

        yield 'multiple covered lines none individually cover node' => [
            [],
            [
                new NodePosition(
                    startLine: 1,
                    startTokenPosition: 10,
                    endLine: 5,
                    endTokenPosition: 20,
                ),
                new NodePosition(
                    startLine: 4,
                    startTokenPosition: 10,
                    endLine: 8,
                    endTokenPosition: 20,
                ),
            ],
            new NodePosition(
                startLine: 3,
                startTokenPosition: 10,
                endLine: 7,
                endTokenPosition: 20,
            ),
            false,
        ];

        yield 'single line exact match of an ignored node type' => [
            [Name::class],
            [
                new NodePosition(
                    startLine: 5,
                    startTokenPosition: 5,
                    endLine: 5,
                    endTokenPosition: 20,
                ),
            ],
            new NodePosition(
                startLine: 5,
                startTokenPosition: 5,
                endLine: 5,
                endTokenPosition: 20,
            ),
            true,
        ];

        yield 'no coverage of an ignored node type' => [
            [Name::class],
            [],
            new NodePosition(
                startLine: 5,
                startTokenPosition: 5,
                endLine: 5,
                endTokenPosition: 20,
            ),
            true,
        ];
    }

    private static function createNode(NodePosition $position): Node
    {
        return new Name(
            'Infection\Tests\Virtual',
            [
                'startLine' => $position->startLine,
                'endLine' => $position->endLine,
                'startTokenPos' => $position->startTokenPosition,
                'endTokenPos' => $position->endTokenPosition,
            ],
        );
    }
}
