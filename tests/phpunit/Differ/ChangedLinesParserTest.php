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

use Infection\Differ\ChangedLinesParser;
use Infection\Differ\ChangedLinesRange;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ChangedLinesParser::class)]
final class ChangedLinesParserTest extends TestCase
{
    /**
     * @param string[] $lines
     * @param array<string, list<ChangedLinesRange>> $expected
     */
    #[DataProvider('diffProvider')]
    public function test_it_parses_the_changed_lines_of_a_diff(array $lines, array $expected): void
    {
        $actual = ChangedLinesParser::parse($lines, '/path/to/project');

        $this->assertEquals($expected, $actual);
    }

    public static function diffProvider(): iterable
    {
        yield 'empty diff' => [
            [],
            [],
        ];

        yield 'added and modified lines' => [
            [
                'diff --git a/src/Foo.php b/src/Foo.php',
                'index 1111111..2222222 100644',
                '--- a/src/Foo.php',
                '+++ b/src/Foo.php',
                '@@ -10,2 +10,3 @@ final class Foo',
                '-        return 1;',
                '-        return 2;',
                '+        return 10;',
                '+        return 20;',
                '+        return 30;',
                '@@ -40 +41 @@ final class Foo',
                '-        return 3;',
                '+        return 40;',
            ],
            [
                '/path/to/project/src/Foo.php' => [
                    ChangedLinesRange::forRange(10, 3),
                    ChangedLinesRange::forLine(41),
                ],
            ],
        ];

        yield 'only removed lines' => [
            [
                'diff --git a/src/Foo.php b/src/Foo.php',
                'index 1111111..2222222 100644',
                '--- a/src/Foo.php',
                '+++ b/src/Foo.php',
                '@@ -40 +39,0 @@ final class Foo',
                '-        return 3;',
            ],
            [],
        ];

        yield 'several files' => [
            [
                'diff --git a/src/Foo.php b/src/Foo.php',
                'index 1111111..2222222 100644',
                '--- a/src/Foo.php',
                '+++ b/src/Foo.php',
                '@@ -3,0 +4,2 @@ final class Foo',
                '+    // first',
                '+    // second',
                'diff --git a/src/Bar.php b/src/Bar.php',
                'new file mode 100644',
                'index 0000000..3333333',
                '--- /dev/null',
                '+++ b/src/Bar.php',
                '@@ -0,0 +1,5 @@',
                '+<?php',
                '+',
                '+final class Bar',
                '+{',
                '+}',
            ],
            [
                '/path/to/project/src/Foo.php' => [ChangedLinesRange::forRange(4, 2)],
                '/path/to/project/src/Bar.php' => [ChangedLinesRange::forRange(1, 5)],
            ],
        ];
    }
}
