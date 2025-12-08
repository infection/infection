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

namespace Infection\Tests\Git;

use Exception;
use Infection\Differ\ChangedLinesRange;
use Infection\Framework\Str;
use Infection\Git\CommandLineGit;
use Infection\Git\Git;
use Infection\Process\ShellCommandLineExecutor;
use Infection\Source\Exception\NoSourceFound;
use Infection\Tests\Process\Exception\GenericProcessException;
use function is_string;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Psr\Log\Test\TestLogger;

#[CoversClass(CommandLineGit::class)]
final class CommandLineGitTest extends TestCase
{
    private ShellCommandLineExecutor&MockObject $commandLineMock;

    private TestLogger $logger;

    private Git $git;

    protected function setUp(): void
    {
        $this->commandLineMock = $this->createMock(ShellCommandLineExecutor::class);
        $this->logger = new TestLogger();

        $this->git = new CommandLineGit(
            $this->commandLineMock,
            $this->logger,
        );
    }

    public function test_it_throws_no_code_to_mutate_exception_when_diff_is_empty(): void
    {
        $this->commandLineMock
            ->method('execute')
            ->willReturn('');

        $this->expectException(NoSourceFound::class);

        $this->git->getChangedFileRelativePaths('AM', 'master', ['src/']);
    }

    public function test_it_gets_the_merge_base(): void
    {
        $expected = 'af25a159143aadacf4d875a3114014e99053430';

        $this->commandLineMock
            ->method('execute')
            ->with(['git', 'merge-base', 'main', 'HEAD'])
            ->willReturn($expected);

        $actual = $this->git->getBaseReference('main');

        $this->assertSame($expected, $actual);
    }

    public function test_it_falls_back_to_the_given_branch_when_no_merge_base_could_be_found(): void
    {
        $exception = new GenericProcessException('fatal!');

        $expectedRecords = [
            [
                'level' => LogLevel::INFO,
                'message' => 'Could not find a common ancestor commit between "main" and "HEAD" and fell back to the base "main". This can if there is no common ancestor commit or if we are in a shallow commit.',
                'context' => ['exception' => $exception],
            ],
        ];

        $this->commandLineMock
            ->method('execute')
            ->with(['git', 'merge-base', 'main', 'HEAD'])
            ->willThrowException($exception);

        $actual = $this->git->getBaseReference('main');

        $this->assertSame('main', $actual);
        $this->assertEquals($expectedRecords, $this->logger->records);
    }

    public function test_it_gets_the_relative_paths_of_the_changed_files_as_a_string(): void
    {
        $this->commandLineMock
            ->method('execute')
            ->with(
                [
                    'git',
                    '--no-pager',
                    'diff',
                    'main',
                    '--no-ext-diff',
                    '--no-color',
                    '--name-only',
                    '--diff-filter=AM',
                    '--',
                    'app/',
                    'my lib/',
                ],
            )
            ->willReturn(
                Str::toSystemLineEndings(
                    <<<'EOF'
                        app/A.php
                        my lib/B.php
                        EOF,
                ),
            );

        $expected = 'app/A.php,my lib/B.php';

        $actual = $this->git->getChangedFileRelativePaths('AM', 'main', ['app/', 'my lib/']);

        $this->assertSame($expected, $actual);
    }

    /**
     * @param array<string, array<int, ChangedLinesRange>>|class-string<Exception> $expected
     */
    #[DataProvider('gitChangedLinesRangesProvider')]
    public function test_it_get_the_changed_lines_ranges_by_files_relative_paths(
        string $diff,
        array|string $expected,
    ): void {
        if (is_string($expected)) {
            $this->expectException($expected);
        }

        $this->commandLineMock
            ->method('execute')
            ->with([
                'git',
                '--no-pager',
                'diff',
                'main',
                '--no-ext-diff',
                '--no-color',
                '--unified=0',
                '--diff-filter=AM',
                '--',
                'src',
                'lib',
            ])
            ->willReturn($diff);

        $actual = $this->git->getChangedLinesRangesByFileRelativePaths(
            'AM',
            'main',
            ['src', 'lib'],
        );

        if (!is_string($expected)) {
            $this->assertEquals($expected, $actual);
        }
    }

    public static function gitChangedLinesRangesProvider(): iterable
    {
        yield 'empty diff' => [
            '',
            NoSourceFound::class,
        ];

        yield '5 lines removed at L10 in old file, 7 lines added starting at L12 in new file' => [
            <<<'DIFF'
                diff --git a/src/Container.php b/src/Container.php
                index 2a9e281..01cbf04 100644
                --- a/src/Container.php
                +++ b/src/Container.php
                @@ -10,5 +12,7 @@ line change example
                DIFF,
            [
                'src/Container.php' => [ChangedLinesRange::create(12, 18)],
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
                'src/Container.php' => [ChangedLinesRange::create(11, 15)],
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
            NoSourceFound::class,
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
                'src/Container.php' => [ChangedLinesRange::create(10, 11)],
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
                'src/Container.php' => [ChangedLinesRange::create(10, 11)],
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
                'src/Container.php' => [ChangedLinesRange::create(10, 10)],
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
                'src/Container.php' => [ChangedLinesRange::create(10, 10)],
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
                'src/Container.php' => [ChangedLinesRange::create(10, 10)],
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
                'src/Container.php' => [ChangedLinesRange::create(10, 10)],
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
                'src/Container.php' => [ChangedLinesRange::create(1, 18)],
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
            NoSourceFound::class,
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
                'src/Container.php' => [ChangedLinesRange::create(1, 1)],
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
            NoSourceFound::class,
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
                'src/Container.php' => [
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
                'src/Container.php' => [
                    ChangedLinesRange::create(38, 38),
                    ChangedLinesRange::create(534, 535),
                    ChangedLinesRange::create(538, 540),
                    ChangedLinesRange::create(1213, 1217),
                ],
                'src/Differ/FilesDiffChangedLines.php' => [
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
                'src/Git/Git.php' => [
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
                'src/Git/CommandLineGit.php' => [
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
                'tests/phpunit/Git/CommandLineGitTest.php' => [
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
                'src/Source.php' => [ChangedLinesRange::create(11, 15)],
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
                'src/Git/Git.php' => [
                    ChangedLinesRange::create(11, 12),
                ],
                'src/Git/CommandLineGit.php' => [
                    ChangedLinesRange::create(21, 25),
                ],
                'tests/phpunit/Git/CommandLineGitTest.php' => [
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
                @@ -12 +14 @@ use Infection\Process\ShellCommandLineExecutor;
                @@ -25 +28 @@ final class CommandLineGit
                @@ -50 +54 @@ final class CommandLineGit
                DIFF,
            [
                'src/Git/CommandLineGit.php' => [
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
                'src/Git/Git.php' => [
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
                'tests/phpunit/Git/CommandLineGitTest.php' => [
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
                'src/NewLinesAdded.php' => [ChangedLinesRange::create(10, 11)],
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
            NoSourceFound::class,
        ];

        yield 'empty file was added' => [
            <<<'DIFF'
                diff --git a/src/Container.php b/src/Container.php
                new file mode 100644
                index 2a9e281..01cbf04 100644
                DIFF,
            NoSourceFound::class,
        ];
    }

    #[DataProvider('defaultGitBaseProvider')]
    public function test_it_gets_the_default_git_base(
        string|Exception $shellOutputOrException,
        string $expected,
    ): void {
        $expectedRecords = [];

        if (is_string($shellOutputOrException)) {
            $this->commandLineMock
                ->method('execute')
                ->willReturn($shellOutputOrException);
        } else {
            $expectedRecords[] = [
                'level' => LogLevel::INFO,
                'message' => 'Could not find a symbolic reference for "refs/remotes/origin/HEAD".',
                'context' => ['exception' => $shellOutputOrException],
            ];

            $this->commandLineMock
                ->method('execute')
                ->willThrowException($shellOutputOrException);
        }

        $actual = $this->git->getDefaultBase();

        $this->assertSame($expected, $actual);
        $this->assertEquals($expectedRecords, $this->logger->records);
    }

    public static function defaultGitBaseProvider(): iterable
    {
        yield 'nominal' => [
            'refs/remotes/origin/main',
            'refs/remotes/origin/main',
        ];

        yield 'invalid output' => [
            'something-unexpected',
            // We leave it alone, it is likely more correct than our fallback. in the measure
            // that if the git command couldn't figure it out, it will fail the process, so whatever
            // is returned is most likely correct.
            'something-unexpected',
        ];

        yield 'the git command failed due to the name not being a valid symbolic ref' => [
            new GenericProcessException(
                'fatal: ref testBranch is not a symbolic ref',
            ),
            Git::FALLBACK_BASE,
        ];
    }
}
