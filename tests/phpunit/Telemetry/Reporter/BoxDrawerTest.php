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

namespace Infection\Tests\Telemetry\Reporter;

use function count;
use Infection\Telemetry\Reporter\BoxDrawer;
use function is_array;
use const PHP_EOL;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function sprintf;

#[CoversClass(BoxDrawer::class)]
final class BoxDrawerTest extends TestCase
{
    #[DataProvider('linesProvider')]
    public function test_it_can_draw_lines_with_boxes(
        array $lines,
        string $expected,
    ): void {
        $drawer = new BoxDrawer();

        $actual = self::drawLines($drawer, $lines);

        $this->assertSame($expected, $actual, $actual);
    }

    public static function linesProvider(): iterable
    {
        yield 'no lines' => [
            [],
            <<<'OUTPUT'

                OUTPUT,
        ];

        yield 'one root item' => [
            [0],
            <<<'OUTPUT'
                ─ d0

                OUTPUT,
        ];

        yield 'multiple root items' => [
            [0, 1, 2],
            <<<'OUTPUT'
                ┌─ d0
                ├─ d1
                └─ d2

                OUTPUT,
        ];

        yield 'one root item with child' => [
            [
                0 => [1],
            ],
            <<<'OUTPUT'
                ─ d0
                    └─ d1

                OUTPUT,
        ];

        yield 'one root item with children' => [
            [
                0 => [
                    1,
                    2,
                    3,
                ],
            ],
            <<<'OUTPUT'
                ─ d0
                    ├─ d1
                    ├─ d2
                    └─ d3

                OUTPUT,
        ];

        yield 'multiple root items with children' => [
            [
                0 => [
                    1,
                    2,
                    3,
                ],
                4,
                5 => [6],
                7,
            ],
            <<<'OUTPUT'
                ┌─ d0
                │   ├─ d1
                │   ├─ d2
                │   └─ d3
                ├─ d4
                ├─ d5
                │   └─ d6
                └─ d7

                OUTPUT,
        ];

        yield 'multiple root items with nested children' => [
            [
                0 => [
                    1 => [
                        2 => [3],
                    ],
                    4 => [
                        5,
                        6,
                    ],
                    7,
                ],
                8,
                9 => [10],
                11,
            ],
            <<<'OUTPUT'
                ┌─ d0
                │   ├─ d1
                │   │   └─ d2
                │   │       └─ d3
                │   ├─ d4
                │   │   ├─ d5
                │   │   └─ d6
                │   └─ d7
                ├─ d8
                ├─ d9
                │   └─ d10
                └─ d11

                OUTPUT,
        ];

        yield 'single deep nesting to test connector caching' => [
            [
                0 => [
                    1 => [
                        2 => [
                            3 => [4],
                        ],
                    ],
                ],
            ],
            <<<'OUTPUT'
                ─ d0
                    └─ d1
                        └─ d2
                            └─ d3
                                └─ d4

                OUTPUT,
        ];

        yield 'complex nesting with history management edge cases' => [
            [
                0 => [
                    1 => [2],
                    3,
                ],
                4 => [
                    5 => [
                        6,
                        7,
                    ],
                ],
                8,
            ],
            <<<'OUTPUT'
                ┌─ d0
                │   ├─ d1
                │   │   └─ d2
                │   └─ d3
                ├─ d4
                │   └─ d5
                │       ├─ d6
                │       └─ d7
                └─ d8

                OUTPUT,
        ];

        yield 'edge case' => [
            [
                0 => [
                    1 => [2, 3],
                    4 => [5, 6],
                ],
                7 => [
                    8 => [9, 10],
                    11 => [12, 13],
                ],
                14,
            ],
            <<<'OUTPUT'
                ┌─ d0
                │   ├─ d1
                │   │   ├─ d2
                │   │   └─ d3
                │   └─ d4
                │       ├─ d5
                │       └─ d6
                ├─ d7
                │   ├─ d8
                │   │   ├─ d9
                │   │   └─ d10
                │   └─ d11
                │       ├─ d12
                │       └─ d13
                └─ d14

                OUTPUT,
        ];
    }

    /**
     * @param list<positive-int|0> $lines
     */
    private static function drawLines(
        BoxDrawer $drawer,
        array $lines,
        $depth = 0,
        $result = '',
    ): string {
        $linesCount = count($lines);
        $sequenceIndex = 0;

        // The index here may be non-sequential, so we can't use it to determine the last item
        foreach ($lines as $index => $item) {
            if (is_array($item)) {
                $result .= sprintf(
                    '%s d%d%s',
                    $drawer->draw(
                        $depth,
                        isLast: $sequenceIndex === $linesCount - 1,
                    ),
                    $index,
                    PHP_EOL,
                );

                $result = self::drawLines(
                    $drawer,
                    $item,
                    $depth + 1,
                    $result,
                );
            } else {
                $result .= sprintf(
                    '%s d%d%s',
                    $drawer->draw(
                        $depth,
                        isLast: $sequenceIndex === $linesCount - 1,
                    ),
                    $item,
                    PHP_EOL,
                );
            }

            ++$sequenceIndex;
        }

        return $result;
    }
}
