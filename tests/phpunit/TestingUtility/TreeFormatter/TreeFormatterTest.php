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

namespace Infection\Tests\TestingUtility\TreeFormatter;

use function implode;
use Infection\Tests\TestingUtility\TreeFormatter\UnicodeTreeDiagramDrawer\UnicodeTreeDiagramDrawer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function Pipeline\take;
use function sprintf;

#[CoversClass(TreeFormatter::class)]
final class TreeFormatterTest extends TestCase
{
    /**
     * @param TestNode[] $nodes
     */
    #[DataProvider('treeProvider')]
    public function test_it_can_format_tree_structures(
        array $nodes,
        string $expected,
    ): void {
        $formatter = new TreeFormatter(
            new UnicodeTreeDiagramDrawer(),
            static fn (TestNode $node): string => sprintf('n%d', $node->id),
            static fn (TestNode $node): iterable => $node->children,
        );

        $actual = implode(
            "\n",
            take($formatter->render($nodes))->toList(),
        );

        $this->assertSame($expected, $actual);
    }

    public static function treeProvider(): iterable
    {
        yield 'no nodes' => [
            [],
            <<<'OUTPUT'

                OUTPUT,
        ];

        yield 'single root node without children' => [
            [new TestNode(0)],
            <<<'OUTPUT'
                ─ n0

                OUTPUT,
        ];

        yield 'multiple root nodes without children' => [
            [
                new TestNode(0),
                new TestNode(1),
                new TestNode(2),
            ],
            <<<'OUTPUT'
                ┌─ n0
                ├─ n1
                └─ n2

                OUTPUT,
        ];

        yield 'single root node with one child' => [
            [
                new TestNode(0, [
                    new TestNode(1),
                ]),
            ],
            <<<'OUTPUT'
                ─ n0
                    └─ n1

                OUTPUT,
        ];

        yield 'single root node with multiple children' => [
            [
                new TestNode(0, [
                    new TestNode(1),
                    new TestNode(2),
                    new TestNode(3),
                ]),
            ],
            <<<'OUTPUT'
                ─ n0
                    ├─ n1
                    ├─ n2
                    └─ n3

                OUTPUT,
        ];

        yield 'multiple root nodes with children' => [
            [
                new TestNode(0, [
                    new TestNode(1),
                    new TestNode(2),
                    new TestNode(3),
                ]),
                new TestNode(4),
                new TestNode(5, [
                    new TestNode(6),
                ]),
                new TestNode(7),
            ],
            <<<'OUTPUT'
                ┌─ n0
                │   ├─ n1
                │   ├─ n2
                │   └─ n3
                ├─ n4
                ├─ n5
                │   └─ n6
                └─ n7

                OUTPUT,
        ];

        yield 'deeply nested structure' => [
            [
                new TestNode(0, [
                    new TestNode(1, [
                        new TestNode(2, [
                            new TestNode(3),
                        ]),
                    ]),
                    new TestNode(4, [
                        new TestNode(5),
                        new TestNode(6),
                    ]),
                    new TestNode(7),
                ]),
                new TestNode(8),
                new TestNode(9, [
                    new TestNode(10),
                ]),
                new TestNode(11),
            ],
            <<<'OUTPUT'
                ┌─ n0
                │   ├─ n1
                │   │   └─ n2
                │   │       └─ n3
                │   ├─ n4
                │   │   ├─ n5
                │   │   └─ n6
                │   └─ n7
                ├─ n8
                ├─ n9
                │   └─ n10
                └─ n11

                OUTPUT,
        ];

        yield 'very deep single chain' => [
            [
                new TestNode(0, [
                    new TestNode(1, [
                        new TestNode(2, [
                            new TestNode(3, [
                                new TestNode(4),
                            ]),
                        ]),
                    ]),
                ]),
            ],
            <<<'OUTPUT'
                ─ n0
                    └─ n1
                        └─ n2
                            └─ n3
                                └─ n4

                OUTPUT,
        ];

        yield 'complex branching structure' => [
            [
                new TestNode(0, [
                    new TestNode(1, [
                        new TestNode(2),
                        new TestNode(3),
                    ]),
                    new TestNode(4, [
                        new TestNode(5),
                        new TestNode(6),
                    ]),
                ]),
                new TestNode(7, [
                    new TestNode(8, [
                        new TestNode(9),
                        new TestNode(10),
                    ]),
                    new TestNode(11, [
                        new TestNode(12),
                        new TestNode(13),
                    ]),
                ]),
                new TestNode(14),
            ],
            <<<'OUTPUT'
                ┌─ n0
                │   ├─ n1
                │   │   ├─ n2
                │   │   └─ n3
                │   └─ n4
                │       ├─ n5
                │       └─ n6
                ├─ n7
                │   ├─ n8
                │   │   ├─ n9
                │   │   └─ n10
                │   └─ n11
                │       ├─ n12
                │       └─ n13
                └─ n14

                OUTPUT,
        ];
    }
}
