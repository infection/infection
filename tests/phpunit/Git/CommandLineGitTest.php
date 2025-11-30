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
use function implode;
use Infection\Framework\Str;
use Infection\Git\CommandLineGit;
use Infection\Git\Git;
use Infection\Git\NoFilesInDiffToMutate;
use Infection\Process\ShellCommandLineExecutor;
use function is_string;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use function sprintf;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\RuntimeException as SymfonyProcessRuntimeException;

#[CoversClass(CommandLineGit::class)]
final class CommandLineGitTest extends TestCase
{
    public function test_it_throws_no_code_to_mutate_exception_when_diff_is_empty(): void
    {
        $commandLineMock = $this->createMock(ShellCommandLineExecutor::class);
        $commandLineMock
            ->method('execute')
            ->willReturn('');

        $diffProvider = new CommandLineGit($commandLineMock);

        $this->expectException(NoFilesInDiffToMutate::class);
        $diffProvider->getChangedFileRelativePaths('AM', 'master', ['src/']);
    }

    public function test_it_gets_the_relative_paths_of_the_changed_files_as_a_string(): void
    {
        $expectedMergeBaseCommandLine = ['git', 'merge-base', 'master', 'HEAD'];
        $mergeBaseCommandLineOutput = '0ABCMERGE_BASE_342';

        $expectedDiffCommandLine = ['git', 'diff', $mergeBaseCommandLineOutput, '--diff-filter', 'AM', '--name-only', '--', 'app/', 'my lib/'];
        $diffCommandLineOutput = Str::toSystemLineEndings(
            <<<'EOF'
                app/A.php
                my lib/B.php
                EOF,
        );

        $commandLineMock = $this->createMock(ShellCommandLineExecutor::class);
        $commandLineMock
            ->method('execute')
            ->willReturnCallback(
                fn (array $command): string => match ($command) {
                    $expectedMergeBaseCommandLine => $mergeBaseCommandLineOutput,
                    $expectedDiffCommandLine => $diffCommandLineOutput,
                    default => $this->failForUnexpectedShellCommand($command),
                },
            );

        $diffProvider = new CommandLineGit($commandLineMock);

        $expected = 'app/A.php,my lib/B.php';

        $actual = $diffProvider->getChangedFileRelativePaths('AM', 'master', ['app/', 'my lib/']);

        $this->assertSame($expected, $actual);
    }

    public function test_it_uses_the_base_branch_passed_as_the_reference_commit_when_gets_the_relative_paths_of_the_changed_files_if_getting_the_merge_base_failed(): void
    {
        $expectedMergeBaseCommandLine = ['git', 'merge-base', 'master', 'HEAD'];
        $mergeBaseCommandLineException = $this->createMock(ProcessFailedException::class);

        $expectedDiffCommandLine = ['git', 'diff', 'master', '--diff-filter', 'AM', '--name-only', '--', 'app/', 'my lib/'];
        $diffCommandLineOutput = Str::toSystemLineEndings(
            <<<'EOF'
                app/A.php
                my lib/B.php
                EOF,
        );

        $commandLineMock = $this->createMock(ShellCommandLineExecutor::class);
        $commandLineMock
            ->method('execute')
            ->willReturnCallback(
                fn (array $command): string => match ($command) {
                    $expectedMergeBaseCommandLine => throw $mergeBaseCommandLineException,
                    $expectedDiffCommandLine => $diffCommandLineOutput,
                    default => $this->failForUnexpectedShellCommand($command),
                },
            );

        $diffProvider = new CommandLineGit($commandLineMock);

        $expected = 'app/A.php,my lib/B.php';

        $actual = $diffProvider->getChangedFileRelativePaths('AM', 'master', ['app/', 'my lib/']);

        $this->assertSame($expected, $actual);
    }

    public function test_it_fails_at_getting_the_relative_paths_of_the_changed_files_if_getting_the_merge_base_failed_unexpectedly(): void
    {
        $expectedMergeBaseCommandLine = ['git', 'merge-base', 'master', 'HEAD'];
        $mergeBaseCommandLineException = new SymfonyProcessRuntimeException('fatal: Not a valid object name randomName');

        $commandLineMock = $this->createMock(ShellCommandLineExecutor::class);
        $commandLineMock
            ->method('execute')
            ->with($expectedMergeBaseCommandLine)
            ->willThrowException($mergeBaseCommandLineException);

        $diffProvider = new CommandLineGit($commandLineMock);

        $this->expectExceptionObject($mergeBaseCommandLineException);

        $diffProvider->getChangedFileRelativePaths('AM', 'master', ['app/', 'my lib/']);
    }

    public function test_it_get_the_changed_lines_as_a_string(): void
    {
        $expectedMergeBaseCommandLine = ['git', 'merge-base', 'master', 'HEAD'];
        $mergeBaseCommandLineOutput = '0ABCMERGE_BASE_342';

        $expectedDiffCommandLine = ['git', 'diff', $mergeBaseCommandLineOutput, '--unified=0', '--diff-filter=AM'];
        $diffCommandLineOutput = Str::toSystemLineEndings(
            <<<'EOF'
                diff --git a/tests/FooTest.php b/tests/FooTest.php
                index 2a9e281..01cbf04 100644
                --- a/tests/FooTest.php
                +++ b/tests/FooTest.php
                @@ -73 +73 @@ final class FooTest
                -            return false === \strpos($sql, 'doctrine_migrations');
                +            return ! \str_contains($sql, 'doctrine_migrations');
                diff --git a/Bar.php b/Bar.php
                index f97971a..1ef35a5 100644
                --- a/Bar.php
                +++ b/Bar.php
                @@ -10,0 +11,3 @@ final class Bar
                +    /**
                +     * @var null|non-empty-string
                +     */
                @@ -21 +31,4 @@ final class Bar
                -        return $this->foo = \strrev($encryptedMessage);
                +        $strrev = \strrev($encryptedMessage);

                EOF,
        );

        $commandLineMock = $this->createMock(ShellCommandLineExecutor::class);
        $commandLineMock
            ->method('execute')
            ->willReturnCallback(
                fn (array $command): string => match ($command) {
                    $expectedMergeBaseCommandLine => $mergeBaseCommandLineOutput,
                    $expectedDiffCommandLine => $diffCommandLineOutput,
                    default => $this->failForUnexpectedShellCommand($command),
                },
            );

        $diffProvider = new CommandLineGit($commandLineMock);

        $expected = Str::toSystemLineEndings(
            <<<'EOF'
                diff --git a/tests/FooTest.php b/tests/FooTest.php
                @@ -73 +73 @@ final class FooTest
                diff --git a/Bar.php b/Bar.php
                @@ -10,0 +11,3 @@ final class Bar
                @@ -21 +31,4 @@ final class Bar

                EOF,
        );

        $actual = $diffProvider->provideWithLines('master');

        $this->assertSame($expected, $actual);
    }

    public function test_it_uses_the_base_branch_passed_as_the_reference_commit_when_gets_the_changed_lines_if_getting_the_merge_base_failed(): void
    {
        $expectedMergeBaseCommandLine = ['git', 'merge-base', 'master', 'HEAD'];
        $mergeBaseCommandLineException = $this->createMock(ProcessFailedException::class);

        $expectedDiffCommandLine = ['git', 'diff', 'master', '--unified=0', '--diff-filter=AM'];
        $diffCommandLineOutput = Str::toSystemLineEndings(
            <<<'EOF'
                diff --git a/tests/FooTest.php b/tests/FooTest.php
                index 2a9e281..01cbf04 100644
                --- a/tests/FooTest.php
                +++ b/tests/FooTest.php
                @@ -73 +73 @@ final class FooTest
                -            return false === \strpos($sql, 'doctrine_migrations');
                +            return ! \str_contains($sql, 'doctrine_migrations');

                EOF,
        );

        $commandLineMock = $this->createMock(ShellCommandLineExecutor::class);
        $commandLineMock
            ->method('execute')
            ->willReturnCallback(
                fn (array $command): string => match ($command) {
                    $expectedMergeBaseCommandLine => throw $mergeBaseCommandLineException,
                    $expectedDiffCommandLine => $diffCommandLineOutput,
                    default => $this->failForUnexpectedShellCommand($command),
                },
            );

        $diffProvider = new CommandLineGit($commandLineMock);

        $expected = Str::toSystemLineEndings(
            <<<'EOF'
                diff --git a/tests/FooTest.php b/tests/FooTest.php
                @@ -73 +73 @@ final class FooTest

                EOF,
        );

        $actual = $diffProvider->provideWithLines('master');

        $this->assertSame($expected, $actual);
    }

    public function test_it_fails_at_getting_the_modified_lines_if_getting_the_merge_base_failed_unexpectedly(): void
    {
        $expectedMergeBaseCommandLine = ['git', 'merge-base', 'master', 'HEAD'];
        $mergeBaseCommandLineException = new SymfonyProcessRuntimeException('fatal: Not a valid object name randomName');

        $commandLineMock = $this->createMock(ShellCommandLineExecutor::class);
        $commandLineMock
            ->method('execute')
            ->with($expectedMergeBaseCommandLine)
            ->willThrowException($mergeBaseCommandLineException);

        $diffProvider = new CommandLineGit($commandLineMock);

        $this->expectExceptionObject($mergeBaseCommandLineException);

        $diffProvider->provideWithLines('master');
    }

    #[Group('integration')]
    public function test_it_can_get_this_project_default_base_branch(): void
    {
        $diffProvider = new CommandLineGit(new ShellCommandLineExecutor());

        $this->assertSame('origin/master', $diffProvider->getDefaultBaseBranch());
    }

    #[DataProvider('defaultBaseBranchProvider')]
    public function test_it_gets_the_default_base_branch(
        string|Exception $shellOutputOrException,
        string $expected,
    ): void {
        $commandLineMock = $this->createMock(ShellCommandLineExecutor::class);

        if (is_string($shellOutputOrException)) {
            $commandLineMock
                ->method('execute')
                ->willReturn($shellOutputOrException);
        } else {
            $commandLineMock
                ->method('execute')
                ->willThrowException($shellOutputOrException);
        }

        $diffProvider = new CommandLineGit($commandLineMock);

        $actual = $diffProvider->getDefaultBaseBranch();

        $this->assertSame($expected, $actual);
    }

    public static function defaultBaseBranchProvider(): iterable
    {
        yield 'nominal' => [
            'refs/remotes/origin/main',
            'origin/main',
        ];

        yield 'invalid output (this is possible but not with the options we pass)' => [
            'upstream/main',
            Git::FALLBACK_BASE_BRANCH,
        ];

        yield 'invalid output (not understandable)' => [
            'something-unexpected',
            Git::FALLBACK_BASE_BRANCH,
        ];

        yield 'invalid output (empty)' => [
            '',
            Git::FALLBACK_BASE_BRANCH,
        ];

        yield 'the git command failed due to the name not being a valid symbolic ref' => [
            new RuntimeException(
                'fatal: ref testBranch is not a symbolic ref',
            ),
            Git::FALLBACK_BASE_BRANCH,
        ];
    }

    /**
     * @param string[] $command
     */
    private function failForUnexpectedShellCommand(array $command): never
    {
        $this->fail(
            sprintf(
                'Unexpected shell command: %s',
                implode(' ', $command),
            ),
        );
    }
}
