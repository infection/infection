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
use Infection\Git\NoFilesInDiffToMutate;
use Infection\Process\ShellCommandLineExecutor;
use Infection\Tests\Process\Exception\GenericProcessException;
use function is_string;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function Safe\realpath;

#[CoversClass(CommandLineGit::class)]
final class CommandLineGitTest extends TestCase
{
    private ShellCommandLineExecutor&MockObject $commandLineMock;

    private Git $git;

    protected function setUp(): void
    {
        $this->commandLineMock = $this->createMock(ShellCommandLineExecutor::class);

        $this->git = new CommandLineGit($this->commandLineMock);
    }

    public function test_it_throws_no_code_to_mutate_exception_when_diff_is_empty(): void
    {
        $this->commandLineMock
            ->method('execute')
            ->willReturn('');

        $this->expectException(NoFilesInDiffToMutate::class);

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
        $this->commandLineMock
            ->method('execute')
            ->with(['git', 'merge-base', 'main', 'HEAD'])
            ->willThrowException(new GenericProcessException('fatal!'));

        $actual = $this->git->getBaseReference('main');

        $this->assertSame('main', $actual);
    }

    public function test_it_gets_the_relative_paths_of_the_changed_files_as_a_string(): void
    {
        $this->commandLineMock
            ->method('execute')
            ->with(
                ['git', 'diff', 'main', '--diff-filter', 'AM', '--name-only', '--', 'app/', 'my lib/'],
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

    #[DataProvider('diffLinesProvider')]
    public function test_it_get_the_changed_lines(
        string $diff,
        array $expected,
    ): void {
        $expectedDiffCommandLine = ['git', 'diff', 'main', '--unified=0', '--diff-filter=AM'];

        $this->commandLineMock
            ->method('execute')
            ->with($expectedDiffCommandLine)
            ->willReturn($diff);

        $actual = $this->git->provideWithLines('main');

        $this->assertEquals($expected, $actual);
    }

    public static function diffLinesProvider(): iterable
    {
        yield [
            <<<'EOF'
                diff --git a/src/Container.php b/src/Container.php
                index f97971a..1ef35a5 100644
                --- a/src/Container.php
                +++ b/src/Container.php
                @@ -37,0 +38 @@ namespace Infection;
                @@ -533 +534,2 @@ final class Container
                @@ -535,0 +538,3 @@ final class Container
                @@ -1207,0 +1213,5 @@ final class Container

                EOF,
            [
                realpath(__DIR__ . '/../../../src/Container.php') => [
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
                index f97971a..1ef35a5 100644
                --- a/src/Container.php
                +++ b/src/Container.php
                @@ -37,0 +38 @@ namespace Infection;
                @@ -533 +534,2 @@ final class Container
                @@ -535,0 +538,3 @@ final class Container
                @@ -1207,0 +1213,5 @@ final class Container
                diff --git a/src/Git/CommandLineGit.php b/src/Git/CommandLineGit.php
                index f97971a..1ef35a5 100644
                --- a/src/Git/CommandLineGit.php
                +++ b/src/Git/CommandLineGit.php
                new file mode 100644
                @@ -0,0 +1,18 @@
                DIFF,
            [
                realpath(__DIR__ . '/../../../src/Container.php') => [
                    new ChangedLinesRange(38, 38),
                    new ChangedLinesRange(534, 535),
                    new ChangedLinesRange(538, 540),
                    new ChangedLinesRange(1213, 1217),
                ],
                realpath(__DIR__ . '/../../../src/Git/CommandLineGit.php') => [
                    new ChangedLinesRange(1, 18),
                ],
            ],
        ];
    }

    #[DataProvider('defaultGitBaseProvider')]
    public function test_it_gets_the_default_git_base(
        string|Exception $shellOutputOrException,
        string $expected,
    ): void {
        if (is_string($shellOutputOrException)) {
            $this->commandLineMock
                ->method('execute')
                ->willReturn($shellOutputOrException);
        } else {
            $this->commandLineMock
                ->method('execute')
                ->willThrowException($shellOutputOrException);
        }

        $actual = $this->git->getDefaultBase();

        $this->assertSame($expected, $actual);
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
