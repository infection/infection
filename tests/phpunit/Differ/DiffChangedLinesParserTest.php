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

use Generator;
use Infection\Differ\ChangedLinesRange;
use Infection\Differ\DiffChangedLinesParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function Safe\realpath;

#[Group('integration')]
#[CoversClass(DiffChangedLinesParser::class)]
final class DiffChangedLinesParserTest extends TestCase
{
    /**
     * @param array<string, array<int, ChangedLinesRange>> $expected
     */
    #[DataProvider('provideDiffs')]
    public function test_it_converts_diff_to_files_and_changed_lines_map(
        string $diff,
        array $expected,
    ): void {
        $collector = new DiffChangedLinesParser();

        $actual = $collector->parse($diff);

        $this->assertEquals($expected, $actual);
    }

    public static function provideDiffs(): Generator
    {
        yield 'empty diff' => [
            '',
            [],
        ];

        yield 'one file with added lines in different places' => [
            <<<'DIFF'
                diff --git a/src/Container.php b/src/Container.php
                @@ -37,0 +38 @@ namespace Infection;
                @@ -533 +534,2 @@ final class Container
                @@ -535,0 +538,3 @@ final class Container
                @@ -1207,0 +1213,5 @@ final class Container
                DIFF,
            [
                realpath('src/Container.php') => [
                    new ChangedLinesRange(38, 38),
                    new ChangedLinesRange(534, 535),
                    new ChangedLinesRange(538, 540),
                    new ChangedLinesRange(1213, 1217),
                ],
            ],
        ];

        yield 'two files, second one is new created' => [
            <<<'DIFF'
                diff --git a/src/Container.php b/src/Container.php
                @@ -37,0 +38 @@ namespace Infection;
                @@ -533 +534,2 @@ final class Container
                @@ -535,0 +538,3 @@ final class Container
                @@ -1207,0 +1213,5 @@ final class Container
                diff --git a/src/Differ/FilesDiffChangedLines.php b/src/Differ/FilesDiffChangedLines.php
                new file mode 100644
                @@ -0,0 +1,18 @@
                DIFF,
            [
                realpath('src/Container.php') => [
                    new ChangedLinesRange(38, 38),
                    new ChangedLinesRange(534, 535),
                    new ChangedLinesRange(538, 540),
                    new ChangedLinesRange(1213, 1217),
                ],
                realpath('src/Differ/FilesDiffChangedLines.php') => [
                    new ChangedLinesRange(1, 18),
                ],
            ],
        ];

        yield 'single line modification simple format' => [
            <<<'DIFF'
                diff --git a/src/Git/Git.php b/src/Git/Git.php
                index abc123..def456 100644
                --- a/src/Git/Git.php
                +++ b/src/Git/Git.php
                @@ -50 +51 @@ interface Git
                DIFF,
            [
                realpath(__DIR__ . '/../../../src/Git/Git.php') => [
                    new ChangedLinesRange(51, 51),
                ],
            ],
        ];

        yield 'addition at start of file' => [
            <<<'DIFF'
                diff --git a/src/Git/CommandLineGit.php b/src/Git/CommandLineGit.php
                new file mode 100644
                index 0000000..abc1234
                --- /dev/null
                +++ b/src/Git/CommandLineGit.php
                @@ -0,0 +1,5 @@
                DIFF,
            [
                realpath(__DIR__ . '/../../../src/Git/CommandLineGit.php') => [
                    new ChangedLinesRange(1, 5),
                ],
            ],
        ];

        yield 'large line numbers' => [
            <<<'DIFF'
                diff --git a/tests/phpunit/Git/CommandLineGitTest.php b/tests/phpunit/Git/CommandLineGitTest.php
                index abc123..def456 100644
                --- a/tests/phpunit/Git/CommandLineGitTest.php
                +++ b/tests/phpunit/Git/CommandLineGitTest.php
                @@ -10000 +10001,3 @@ namespace Infection\Tests\Git;
                @@ -15234,0 +15238,10 @@ final class CommandLineGitTest
                DIFF,
            [
                realpath(__DIR__ . '/../../../tests/phpunit/Git/CommandLineGitTest.php') => [
                    new ChangedLinesRange(10001, 10003),
                    new ChangedLinesRange(15238, 15247),
                ],
            ],
        ];

        yield 'three files' => [
            <<<'DIFF'
                diff --git a/src/Git/Git.php b/src/Git/Git.php
                index abc123..def456 100644
                --- a/src/Git/Git.php
                +++ b/src/Git/Git.php
                @@ -10 +11,2 @@ namespace Infection\Git;
                diff --git a/src/Git/CommandLineGit.php b/src/Git/CommandLineGit.php
                index 111222..333444 100644
                --- a/src/Git/CommandLineGit.php
                +++ b/src/Git/CommandLineGit.php
                @@ -20,0 +21,5 @@ final class CommandLineGit
                diff --git a/tests/phpunit/Git/CommandLineGitTest.php b/tests/phpunit/Git/CommandLineGitTest.php
                index aaa111..bbb222 100644
                --- a/tests/phpunit/Git/CommandLineGitTest.php
                +++ b/tests/phpunit/Git/CommandLineGitTest.php
                @@ -100 +101 @@ final class CommandLineGitTest
                @@ -200 +202,3 @@ final class CommandLineGitTest
                DIFF,
            [
                realpath(__DIR__ . '/../../../src/Git/Git.php') => [
                    new ChangedLinesRange(11, 12),
                ],
                realpath(__DIR__ . '/../../../src/Git/CommandLineGit.php') => [
                    new ChangedLinesRange(21, 25),
                ],
                realpath(__DIR__ . '/../../../tests/phpunit/Git/CommandLineGitTest.php') => [
                    new ChangedLinesRange(101, 101),
                    new ChangedLinesRange(202, 204),
                ],
            ],
        ];

        yield 'multiple single-line changes in one file' => [
            <<<'DIFF'
                diff --git a/src/Git/CommandLineGit.php b/src/Git/CommandLineGit.php
                index abc123..def456 100644
                --- a/src/Git/CommandLineGit.php
                +++ b/src/Git/CommandLineGit.php
                @@ -5 +6 @@ namespace Infection\Git;
                @@ -12 +14 @@ use Infection\Process\ShellCommandLineExecutor;
                @@ -25 +28 @@ final class CommandLineGit
                @@ -50 +54 @@ final class CommandLineGit
                DIFF,
            [
                realpath(__DIR__ . '/../../../src/Git/CommandLineGit.php') => [
                    new ChangedLinesRange(6, 6),
                    new ChangedLinesRange(14, 14),
                    new ChangedLinesRange(28, 28),
                    new ChangedLinesRange(54, 54),
                ],
            ],
        ];

        yield 'file with only one hunk' => [
            <<<'DIFF'
                diff --git a/src/Git/Git.php b/src/Git/Git.php
                index abc123..def456 100644
                --- a/src/Git/Git.php
                +++ b/src/Git/Git.php
                @@ -42,0 +43,8 @@ interface Git
                DIFF,
            [
                realpath(__DIR__ . '/../../../src/Git/Git.php') => [
                    new ChangedLinesRange(43, 50),
                ],
            ],
        ];

        yield 'mixed format hunks with ranges and single lines' => [
            <<<'DIFF'
                diff --git a/tests/phpunit/Git/CommandLineGitTest.php b/tests/phpunit/Git/CommandLineGitTest.php
                index abc123..def456 100644
                --- a/tests/phpunit/Git/CommandLineGitTest.php
                +++ b/tests/phpunit/Git/CommandLineGitTest.php
                @@ -10 +11 @@ namespace Infection\Tests\Git;
                @@ -20,0 +22,3 @@ use PHPUnit\Framework\TestCase;
                @@ -30 +34,5 @@ final class CommandLineGitTest
                @@ -45,2 +51 @@ final class CommandLineGitTest
                @@ -60 +66 @@ final class CommandLineGitTest
                @@ -75,0 +82,10 @@ final class CommandLineGitTest
                DIFF,
            [
                realpath(__DIR__ . '/../../../tests/phpunit/Git/CommandLineGitTest.php') => [
                    new ChangedLinesRange(11, 11),
                    new ChangedLinesRange(22, 24),
                    new ChangedLinesRange(34, 38),
                    new ChangedLinesRange(51, 51),
                    new ChangedLinesRange(66, 66),
                    new ChangedLinesRange(82, 91),
                ],
            ],
        ];
    }
}
