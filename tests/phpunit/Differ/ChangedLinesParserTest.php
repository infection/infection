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

use function explode;
use Infection\Differ\ChangedLinesParser;
use Infection\Differ\ChangedLinesRange;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ChangedLinesParser::class)]
final class ChangedLinesParserTest extends TestCase
{
    /**
     * @param array<string, list<ChangedLinesRange>> $expected
     */
    #[DataProvider('diffProvider')]
    public function test_it_parses_the_changed_lines_of_a_diff(string $diff, array $expected): void
    {
        $lines = explode("\n", $diff);

        $actual = ChangedLinesParser::parse($lines, '/path/to/project');

        $this->assertEquals($expected, $actual);
    }

    public function test_it_fails_on_a_hunk_before_any_diff_line(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The following hunk line comes before any diff line: "@@ -1 +1 @@"');

        ChangedLinesParser::parse(['@@ -1 +1 @@'], '/path/to/project');
    }

    public function test_it_fails_on_a_context_line(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a diff without context lines, e.g. the output of "git diff --unified=0", but found the following context line: "     return 1;"');

        ChangedLinesParser::parse(
            [
                'diff --git a/src/Foo.php b/src/Foo.php',
                '@@ -10,2 +10,2 @@ final class Foo',
                '     return 1;',
                '-    return 2;',
                '+    return 3;',
            ],
            '/path/to/project',
        );
    }

    public static function diffProvider(): iterable
    {
        yield 'empty diff' => [
            '',
            [],
        ];

        yield '5 lines removed at L10 in old file, 7 lines added starting at L12 in new file' => [
            <<<'DIFF'
                diff --git a/src/Container.php b/src/Container.php
                index 2a9e281..01cbf04 100644
                --- a/src/Container.php
                +++ b/src/Container.php
                @@ -10,5 +12,7 @@ line change example
                -old line
                +new line
                DIFF,
            [
                '/path/to/project/src/Container.php' => [ChangedLinesRange::create(12, 18)],
            ],
        ];

        yield '5 lines added starting at L11 in new file (0 lines in old file)' => [
            <<<'DIFF'
                diff --git a/src/Container.php b/src/Container.php
                index 2a9e281..01cbf04 100644
                --- a/src/Container.php
                +++ b/src/Container.php
                @@ -10,0 +11,5 @@ line change example
                DIFF,
            [
                '/path/to/project/src/Container.php' => [ChangedLinesRange::create(11, 15)],
            ],
        ];

        yield '5 lines deleted starting at L10 in old file (0 lines in new file)' => [
            <<<'DIFF'
                diff --git a/src/Container.php b/src/Container.php
                index 2a9e281..01cbf04 100644
                --- a/src/Container.php
                +++ b/src/Container.php
                @@ -10,5 +10,0 @@ line change example
                DIFF,
            [],
        ];

        yield 'single line in old file, 2 lines in new file (count of 1 omitted in old)' => [
            <<<'DIFF'
                diff --git a/src/Container.php b/src/Container.php
                index 2a9e281..01cbf04 100644
                --- a/src/Container.php
                +++ b/src/Container.php
                @@ -10 +10,2 @@ line change example
                DIFF,
            [
                '/path/to/project/src/Container.php' => [ChangedLinesRange::create(10, 11)],
            ],
        ];

        yield 'single line in old file, 2 lines in new file (count of 1 explicit in old)' => [
            <<<'DIFF'
                diff --git a/src/Container.php b/src/Container.php
                index 2a9e281..01cbf04 100644
                --- a/src/Container.php
                +++ b/src/Container.php
                @@ -10,1 +10,2 @@ line change example
                DIFF,
            [
                '/path/to/project/src/Container.php' => [ChangedLinesRange::create(10, 11)],
            ],
        ];

        yield '2 lines in old file, single line in new file (count of 1 omitted in new)' => [
            <<<'DIFF'
                diff --git a/src/Container.php b/src/Container.php
                index 2a9e281..01cbf04 100644
                --- a/src/Container.php
                +++ b/src/Container.php
                @@ -10,2 +10 @@ line change example
                DIFF,
            [
                '/path/to/project/src/Container.php' => [ChangedLinesRange::create(10, 10)],
            ],
        ];

        yield '2 lines in old file, single line in new file (count of 1 explicit in new)' => [
            <<<'DIFF'
                diff --git a/src/Container.php b/src/Container.php
                index 2a9e281..01cbf04 100644
                --- a/src/Container.php
                +++ b/src/Container.php
                @@ -10,2 +10,1 @@ line change example
                DIFF,
            [
                '/path/to/project/src/Container.php' => [ChangedLinesRange::create(10, 10)],
            ],
        ];

        yield 'single line changed (count of 1 omitted in both)' => [
            <<<'DIFF'
                diff --git a/src/Container.php b/src/Container.php
                index 2a9e281..01cbf04 100644
                --- a/src/Container.php
                +++ b/src/Container.php
                @@ -10 +10 @@ line change example
                DIFF,
            [
                '/path/to/project/src/Container.php' => [ChangedLinesRange::create(10, 10)],
            ],
        ];

        yield 'single line changed (count of 1 explicit in both)' => [
            <<<'DIFF'
                diff --git a/src/Container.php b/src/Container.php
                index 2a9e281..01cbf04 100644
                --- a/src/Container.php
                +++ b/src/Container.php
                @@ -10,1 +10,1 @@ line change example
                DIFF,
            [
                '/path/to/project/src/Container.php' => [ChangedLinesRange::create(10, 10)],
            ],
        ];

        yield 'new file with 18 lines added' => [
            <<<'DIFF'
                diff --git a/src/Container.php b/src/Container.php
                index 2a9e281..01cbf04 100644
                --- a/src/Container.php
                +++ b/src/Container.php
                @@ -0,0 +1,18 @@ line change example
                DIFF,
            [
                '/path/to/project/src/Container.php' => [ChangedLinesRange::create(1, 18)],
            ],
        ];

        yield 'deleted file with 18 lines removed' => [
            <<<'DIFF'
                diff --git a/src/Container.php b/src/Container.php
                index 2a9e281..01cbf04 100644
                --- a/src/Container.php
                +++ b/src/Container.php
                @@ -1,18 +0,0 @@ line change example
                DIFF,
            [],
        ];

        yield 'single line added at beginning of file (count of 1 omitted)' => [
            <<<'DIFF'
                diff --git a/src/Container.php b/src/Container.php
                index 2a9e281..01cbf04 100644
                --- a/src/Container.php
                +++ b/src/Container.php
                @@ -1,0 +1 @@ line change example
                DIFF,
            [
                '/path/to/project/src/Container.php' => [ChangedLinesRange::create(1, 1)],
            ],
        ];

        yield 'single line deleted at beginning of file (count of 1 omitted)' => [
            <<<'DIFF'
                diff --git a/src/Container.php b/src/Container.php
                index 2a9e281..01cbf04 100644
                --- a/src/Container.php
                +++ b/src/Container.php
                @@ -1 +1,0 @@ line change example
                DIFF,
            [],
        ];

        yield 'one file with added lines in different places' => [
            <<<'DIFF'
                diff --git a/src/Container.php b/src/Container.php
                index 2a9e281..01cbf04 100644
                --- a/src/Container.php
                +++ b/src/Container.php
                @@ -37,0 +38 @@ namespace Infection;
                @@ -533 +534,2 @@ final class Container
                @@ -535,0 +538,3 @@ final class Container
                @@ -1207,0 +1213,5 @@ final class Container
                DIFF,
            [
                '/path/to/project/src/Container.php' => [
                    ChangedLinesRange::create(38, 38),
                    ChangedLinesRange::create(534, 535),
                    ChangedLinesRange::create(538, 540),
                    ChangedLinesRange::create(1213, 1217),
                ],
            ],
        ];

        yield 'two files, second one is new created' => [
            <<<'DIFF'
                diff --git a/src/Container.php b/src/Container.php
                index 2a9e281..01cbf04 100644
                --- a/src/Container.php
                +++ b/src/Container.php
                @@ -37,0 +38 @@ namespace Infection;
                @@ -533 +534,2 @@ final class Container
                @@ -535,0 +538,3 @@ final class Container
                @@ -1207,0 +1213,5 @@ final class Container
                diff --git a/src/Differ/FilesDiffChangedLines.php b/src/Differ/FilesDiffChangedLines.php
                index 2a9e281..01cbf04 100644
                --- a/src/Differ/FilesDiffChangedLines.php
                +++ b/src/Differ/FilesDiffChangedLines.php
                new file mode 100644
                @@ -0,0 +1,18 @@
                DIFF,
            [
                '/path/to/project/src/Container.php' => [
                    ChangedLinesRange::create(38, 38),
                    ChangedLinesRange::create(534, 535),
                    ChangedLinesRange::create(538, 540),
                    ChangedLinesRange::create(1213, 1217),
                ],
                '/path/to/project/src/Differ/FilesDiffChangedLines.php' => [
                    ChangedLinesRange::create(1, 18),
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
                '/path/to/project/src/Git/Git.php' => [
                    ChangedLinesRange::create(51, 51),
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
                '/path/to/project/src/Git/CommandLineGit.php' => [
                    ChangedLinesRange::create(1, 5),
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
                '/path/to/project/tests/phpunit/Git/CommandLineGitTest.php' => [
                    ChangedLinesRange::create(10001, 10003),
                    ChangedLinesRange::create(15238, 15247),
                ],
            ],
        ];

        yield 'one file with all kind of transformations' => [
            <<<'DIFF'
                diff --git a/src/Source.php b/src/Source.php
                index 2a9e281..01cbf04 100644
                --- a/src/Source.php
                +++ b/src/Source.php
                @@ -1,18 +0,0 @@ old lines deleted
                @@ -10,0 +11,5 @@ new lines added
                DIFF,
            [
                '/path/to/project/src/Source.php' => [ChangedLinesRange::create(11, 15)],
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
                '/path/to/project/src/Git/Git.php' => [
                    ChangedLinesRange::create(11, 12),
                ],
                '/path/to/project/src/Git/CommandLineGit.php' => [
                    ChangedLinesRange::create(21, 25),
                ],
                '/path/to/project/tests/phpunit/Git/CommandLineGitTest.php' => [
                    ChangedLinesRange::create(101, 101),
                    ChangedLinesRange::create(202, 204),
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
                @@ -12 +14 @@ use Infection\Process\ShellCommandRunner;
                @@ -25 +28 @@ final class CommandLineGit
                @@ -50 +54 @@ final class CommandLineGit
                DIFF,
            [
                '/path/to/project/src/Git/CommandLineGit.php' => [
                    ChangedLinesRange::create(6, 6),
                    ChangedLinesRange::create(14, 14),
                    ChangedLinesRange::create(28, 28),
                    ChangedLinesRange::create(54, 54),
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
                '/path/to/project/src/Git/Git.php' => [
                    ChangedLinesRange::create(43, 50),
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
                '/path/to/project/tests/phpunit/Git/CommandLineGitTest.php' => [
                    ChangedLinesRange::create(11, 11),
                    ChangedLinesRange::create(22, 24),
                    ChangedLinesRange::create(34, 38),
                    ChangedLinesRange::create(51, 51),
                    ChangedLinesRange::create(66, 66),
                    ChangedLinesRange::create(82, 91),
                ],
            ],
        ];

        yield 'only some files have changed lines in their new files' => [
            <<<'DIFF'
                diff --git a/src/OldLinesDeleted.php b/src/OldLinesDeleted.php
                index 2a9e281..01cbf04 100644
                --- a/src/OldLinesDeleted.php
                +++ b/src/OldLinesDeleted.php
                @@ -1,18 +0,0 @@ line change example
                diff --git a/src/NewLinesAdded.php b/src/NewLinesAdded.php
                index 2a9e281..01cbf04 100644
                --- a/src/NewLinesAdded.php
                +++ b/src/NewLinesAdded.php
                @@ -10,18 +10,2 @@ line change example
                DIFF,
            [
                '/path/to/project/src/NewLinesAdded.php' => [ChangedLinesRange::create(10, 11)],
            ],
        ];

        yield 'file was renamed' => [
            <<<'DIFF'
                diff --git a/src/Container.php b/src/LegacyContainer.php
                index 2a9e281..01cbf04 100644
                --- a/src/Container.php
                +++ b/src/LegacyContainer.php
                DIFF,
            // There is no new or updated code, hence nothing to mutate.
            // While discussing this, we thought _maybe_ there was a very narrow edge case
            // where this _may_ not be true, but it is not compelling enough to change
            // our stance on it.
            // Also note that if other files used the moved file path or symbols, we can
            // expect their usage to change hence to be picked up in the diff.
            [],
        ];

        yield 'empty file was added' => [
            <<<'DIFF'
                diff --git a/src/Container.php b/src/Container.php
                new file mode 100644
                index 2a9e281..01cbf04 100644
                DIFF,
            [],
        ];
    }
}
