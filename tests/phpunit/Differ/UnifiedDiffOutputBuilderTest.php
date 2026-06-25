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

namespace Infection\Tests\Differ;

use Infection\Differ\UnifiedDiffOutputBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Diff\Differ;

#[CoversClass(UnifiedDiffOutputBuilder::class)]
#[Group('integration')]
final class UnifiedDiffOutputBuilderTest extends TestCase
{
    /**
     * @param list<array{string, int}> $diff
     */
    #[DataProvider('diffProvider')]
    public function test_it_builds_a_unified_diff(
        array $diff,
        string $expected,
    ): void {
        $builder = new UnifiedDiffOutputBuilder();

        $this->assertSame($expected, $builder->getDiff($diff));
    }

    public static function diffProvider(): iterable
    {
        yield from self::unifiedDiffProvider();
    }

    public static function unifiedDiffProvider(): iterable
    {
        yield 'empty diff' => [
            [],
            '',
        ];

        yield 'basic diff' => [
            self::createBasicChangeDiff(),
            <<<'DIFF'
                @@ @@
                 line 1
                -line 2
                +changed
                 line 3

                DIFF,
        ];

        yield 'trailing line break is added when the diff does not have one' => [
            [
                ['old', Differ::REMOVED],
                ['new', Differ::ADDED],
            ],
            <<<'DIFF'
                @@ @@
                -old
                +new

                DIFF,
        ];

        yield 'no line end warning tokens are preserved as blank lines' => [
            [
                ["line\n", Differ::OLD],
                ["\n\\ No newline at end of file\n", Differ::NO_LINE_END_EOF_WARNING],
                ["changed\n", Differ::ADDED],
            ],
            <<<'DIFF'
                @@ @@
                 line

                +changed

                DIFF,
        ];

        yield 'no line end warnings are ignored when there is no change' => [
            [
                ["before\n", Differ::OLD],
                ["\n\\ No newline at end of file\n", Differ::NO_LINE_END_EOF_WARNING],
                ["after\n", Differ::OLD],
            ],
            '',
        ];

        yield 'carriage return terminated line ending warnings are preserved' => [
            [
                ["warning\r", Differ::DIFF_LINE_END_WARNING],
            ],
            "@@ @@\n warning\r",
        ];

        yield 'missing line breaks are added for changed lines at the end of a file' => [
            [
                ['old', Differ::REMOVED],
                ['new', Differ::ADDED],
                ['context', Differ::OLD],
            ],
            <<<'DIFF'
                @@ @@
                -old+new context

                DIFF,
        ];

        yield 'missing line breaks are added for added lines before removed lines' => [
            [
                ['new', Differ::ADDED],
                ["old\n", Differ::REMOVED],
            ],
            <<<'DIFF'
                @@ @@
                +new
                -old

                DIFF,
        ];

        yield 'only the latest added and removed lines are checked for missing line breaks' => [
            [
                ['earlier', Differ::ADDED],
                ["context\n", Differ::OLD],
                ['new', Differ::ADDED],
                ["old\n", Differ::REMOVED],
            ],
            <<<'DIFF'
                @@ @@
                +earlier context
                +new
                -old

                DIFF,
        ];

        yield 'distant changes are split into separate hunks' => [
            self::createTwoDistantChangesDiff(),
            <<<'DIFF'
                @@ @@
                 1
                 2
                +A
                 3
                 4
                 5
                @@ @@
                 10
                 11
                 12
                +B
                 13
                 14
                 15

                DIFF,
        ];
    }

    /**
     * @return list<array{string, int}>
     */
    private static function createBasicChangeDiff(): array
    {
        return [
            ["line 1\n", Differ::OLD],
            ["line 2\n", Differ::REMOVED],
            ["changed\n", Differ::ADDED],
            ["line 3\n", Differ::OLD],
        ];
    }

    /**
     * @return list<array{string, int}>
     */
    private static function createTwoDistantChangesDiff(): array
    {
        $diff = [];

        for ($i = 1; $i <= 20; ++$i) {
            $diff[] = ["$i\n", Differ::OLD];

            if ($i === 2) {
                $diff[] = ["A\n", Differ::ADDED];
            }

            if ($i === 12) {
                $diff[] = ["B\n", Differ::ADDED];
            }
        }

        return $diff;
    }
}
