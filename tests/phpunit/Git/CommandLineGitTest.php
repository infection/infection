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
use Infection\Source\Exception\NoSourceFound;
use Infection\TestFramework\Contracts\ShellCommandRunner;
use Infection\Tests\Process\Exception\GenericProcessException;
use function is_string;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Psr\Log\Test\TestLogger;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(CommandLineGit::class)]
final class CommandLineGitTest extends TestCase
{
    private ShellCommandRunner&MockObject $commandLineMock;

    private TestLogger $logger;

    private Git $git;

    protected function setUp(): void
    {
        $this->commandLineMock = $this->createMock(ShellCommandRunner::class);
        $this->logger = new TestLogger();

        $this->git = new CommandLineGit(
            $this->commandLineMock,
            $this->logger,
        );
    }

    public function test_it_throws_no_code_to_mutate_exception_when_diff_is_empty(): void
    {
        $this->commandLineMock
            ->expects($this->exactly(2))
            ->method('mustRun')
            ->willReturnOnConsecutiveCalls('/repository', '')
        ;

        $this->expectException(NoSourceFound::class);

        $this->git->getChangedFilePaths('AM', 'master', ['src/'], '/project');
    }

    public function test_it_gets_the_merge_base(): void
    {
        $expected = 'af25a159143aadacf4d875a3114014e99053430';

        $this->commandLineMock
            ->method('mustRun')
            ->with(['git', 'merge-base', 'main', 'HEAD'])
            ->willReturn($expected)
        ;

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
            ->method('mustRun')
            ->with(['git', 'merge-base', 'main', 'HEAD'])
            ->willThrowException($exception)
        ;

        $actual = $this->git->getBaseReference('main');

        $this->assertSame('main', $actual);
        $this->assertEquals($expectedRecords, $this->logger->records);
    }

    public function test_it_gets_the_absolute_paths_of_the_changed_files(): void
    {
        $this->commandLineMock
            ->expects($this->exactly(2))
            ->method('mustRun')
            ->with($this->logicalOr(
                ['git', '-C', '/project', 'rev-parse', '--show-toplevel'],
                [
                    'git',
                    '-C',
                    '/project',
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
            ))
            ->willReturnOnConsecutiveCalls(
                '/repository',
                Str::toSystemLineEndings(
                    <<<'EOF'
                        project/app/A.php
                        project/my lib/B.php
                        EOF,
                ),
            )
        ;

        $expected = [
            '/repository/project/app/A.php',
            '/repository/project/my lib/B.php',
        ];

        $actual = $this->git->getChangedFilePaths('AM', 'main', ['app/', 'my lib/'], '/project');

        $this->assertSame($expected, $actual);
    }

    /**
     * @param array<string, array<int, ChangedLinesRange>>|class-string<Exception> $expected
     */
    #[DataProvider('gitChangedLinesRangesProvider')]
    public function test_it_gets_the_changed_lines_ranges_by_absolute_file_paths(
        string $diff,
        array|string $expected,
    ): void {
        if (is_string($expected)) {
            $this->expectException($expected);
        }

        $this->commandLineMock
            ->expects($this->exactly(2))
            ->method('mustRun')
            ->with(
                $this->logicalOr(
                    ['git', '-C', '/project', 'rev-parse', '--show-toplevel'],
                    [
                        'git',
                        '-C',
                        '/project',
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
                    ],
                ),
            )
            ->willReturnOnConsecutiveCalls('/repository', $diff)
        ;

        $actual = $this->git->getChangedLinesRangesByFilePaths(
            'AM',
            'main',
            ['src', 'lib'],
            '/project',
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

        yield 'changed lines are parsed relative to the project directory' => [
            <<<'DIFF'
                diff --git a/src/Container.php b/src/Container.php
                index 2a9e281..01cbf04 100644
                --- a/src/Container.php
                +++ b/src/Container.php
                @@ -10,5 +12,7 @@ line change example
                diff --git a/lib/Foo.php b/lib/Foo.php
                --- a/lib/Foo.php
                +++ b/lib/Foo.php
                @@ -1 +1 @@
                DIFF,
            [
                '/repository/src/Container.php' => [ChangedLinesRange::create(12, 18)],
                '/repository/lib/Foo.php' => [ChangedLinesRange::create(1, 1)],
            ],
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
                ->method('mustRun')
                ->willReturn($shellOutputOrException)
            ;
        } else {
            $expectedRecords[] = [
                'level' => LogLevel::INFO,
                'message' => 'Could not find a symbolic reference for "refs/remotes/origin/HEAD".',
                'context' => ['exception' => $shellOutputOrException],
            ];

            $this->commandLineMock
                ->method('mustRun')
                ->willThrowException($shellOutputOrException)
            ;
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

    public function test_it_gets_the_project_directory(): void
    {
        $expected = '/path/to/project';

        $this->commandLineMock
            ->expects($this->once())
            ->method('mustRun')
            ->willReturn($expected)
        ;

        $actual = $this->git->getProjectDirectory();

        $this->assertSame($expected, $actual);
    }
}
