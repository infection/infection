<?php

namespace Infection\DevTools\Git;

use Exception;
use Infection\Framework\Str;
use Infection\Git\CommandLineGit;
use Infection\Git\Git;
use Infection\Logger\GitHub\GitDiffFileProvider;
use Infection\Logger\GitHub\NoFilesInDiffToMutate;
use Infection\Process\ShellCommandLineExecutor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use function implode;
use const PHP_EOL;

#[CoversClass(CommandLineGit::class)]
final class CommandLineGitTest extends TestCase
{
    private ShellCommandLineExecutor&MockObject $commandLineMock;
    private Git $git;

    public function setUp(): void
    {
        $this->commandLineMock = $this->createMock(ShellCommandLineExecutor::class);

        $this->git = new CommandLineGit($this->commandLineMock);
    }

    #[Group('integration')]
    public function test_it_can_get_this_project_default_base_branch(): void
    {
        $git = new CommandLineGit(
            new ShellCommandLineExecutor(),
        );

        $actual = $git->getDefaultBaseBranch();

        $this->assertSame('origin/master', $actual);
    }

    #[DataProvider('defaultBaseBranchProvider')]
    public function test_it_can_get_the_default_base_branch(
        string $processOutput,
        string $expected,
    ): void
    {
        $this->commandLineMock
            ->method('execute')
            ->willReturn($processOutput);

        $actual = $this->git->getDefaultBaseBranch();

        $this->assertSame($expected, $actual);
    }

    public static function defaultBaseBranchProvider(): iterable
    {
        // TODO: check for more cases
        yield ['', 'origin/master'];

        yield ['something/unexpected', 'origin/master'];
    }

    #[DataProvider('unavailableDefaultBaseBranchProvider')]
    public function test_it_returns_the_fallback_when_it_cannot_get_the_default_base_branch_from_git(
        Exception $exception,
        string $expected,
    ): void
    {
        $this->commandLineMock
            ->method('execute')
            ->willThrowException($exception);

        $actual = $this->git->getDefaultBaseBranch();

        $this->assertSame($expected, $actual);
    }

    public static function unavailableDefaultBaseBranchProvider(): iterable
    {
        // TODO: check for a real case example
        yield [
            new RuntimeException('ref refs/remotes/origin/HEAD is not a symbolic ref'),
            Git::FALLBACK_BASE_BRANCH,
        ];
    }

    public function test_it_can_get_the_relative_paths_of_the_changed_files(): void
    {
        $expectedMergeBaseCommandLine = ['git', 'merge-base', 'main', 'HEAD'];
        $expectedDiffCommandLine = ['git', 'diff', '0ABCMERGE_BASE_342', '--diff-filter', 'AM', '--name-only', '--', 'app/', 'my lib/'];

        $this->commandLineMock
            ->method('execute')
            ->willReturnCallback(
                fn (array $command): string => match ($command) {
                    $expectedMergeBaseCommandLine => '0ABCMERGE_BASE_342',
                    $expectedDiffCommandLine => 'app/A.php' . PHP_EOL . 'my lib/B.php',
                    default => $this->fail(
                        'Unexpected shell command: ' . implode(' ', $command),
                    ),
                },
            );

        $expected = [
            'app/A.php',
            'my lib/B.php',
        ];

        $actual = $this->git->getChangedFileRelativePaths(
            'AM',
            'main',
            ['app/', 'my lib/'],
        );

        $this->assertSame($expected, $actual);
    }

    public function test_it_can_get_the_diff_lines(): void
    {
        $expectedMergeBaseCommandLine = ['git', 'merge-base', 'main', 'HEAD'];
        $expectedDiffCommandLine = ['git', 'diff', '0ABCMERGE_BASE_342', '--unified=0', '--diff-filter=AM'];

        $gitUnifiedOutput = Str::toSystemLineEndings(
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

        $expected = Str::toSystemLineEndings(
            <<<'EOF'
            diff --git a/tests/FooTest.php b/tests/FooTest.php
            @@ -73 +73 @@ final class FooTest
            diff --git a/Bar.php b/Bar.php
            @@ -10,0 +11,3 @@ final class Bar
            @@ -21 +31,4 @@ final class Bar

            EOF,
        );

        $this->commandLineMock
            ->method('execute')
            ->willReturnCallback(
                fn (array $command): string => match ($command) {
                    $expectedMergeBaseCommandLine => '0ABCMERGE_BASE_342',
                    $expectedDiffCommandLine => $gitUnifiedOutput,
                    default => $this->fail(
                        'Unexpected shell command: ' . implode(' ', $command),
                    ),
                },
            );

        $actual = $this->git->diffLines('main');

        $this->assertSame($expected, $actual);
    }
}
