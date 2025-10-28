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

namespace Infection\Tests\Logger\GitHub;

use function implode;
use Infection\Logger\GitHub\GitDiffFileProvider;
use Infection\Logger\GitHub\NoFilesInDiffToMutate;
use Infection\Process\ShellCommandLineExecutor;
use Infection\Str;
use const PHP_EOL;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(GitDiffFileProvider::class)]
final class GitDiffFileProviderTest extends TestCase
{
    public function test_it_throws_no_code_to_mutate_exception_when_diff_is_empty(): void
    {
        $shellCommandLineExecutor = $this->createMock(ShellCommandLineExecutor::class);
        $shellCommandLineExecutor
            ->method('execute')
            ->willReturn('');

        $this->expectException(NoFilesInDiffToMutate::class);

        $diffProvider = new GitDiffFileProvider($shellCommandLineExecutor);
        $diffProvider->provide('AM', 'master', ['src/']);
    }

    public function test_it_executes_diff_and_returns_filter_as_a_string(): void
    {
        $expectedMergeBaseCommandLine = ['git', 'merge-base', 'master', 'HEAD'];
        $expectedDiffCommandLine = ['git', 'diff', '0ABCMERGE_BASE_342', '--diff-filter', 'AM', '--name-only', '--', 'app/', 'my lib/'];

        $shellCommandLineExecutor = $this->createMock(ShellCommandLineExecutor::class);

        $shellCommandLineExecutor->expects($this->any())
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

        $diffProvider = new GitDiffFileProvider($shellCommandLineExecutor);
        $filter = $diffProvider->provide('AM', 'master', ['app/', 'my lib/']);

        $this->assertSame('app/A.php,my lib/B.php', $filter);
    }

    public function test_it_provides_lines_filter_as_a_string(): void
    {
        $expectedMergeBaseCommandLine = ['git', 'merge-base', 'master', 'HEAD'];
        $expectedDiffCommandLine = ['git', 'diff', '0ABCMERGE_BASE_342', '--unified=0', '--diff-filter=AM'];

        $shellCommandLineExecutor = $this->createMock(ShellCommandLineExecutor::class);

        $gitUnifiedOutput = <<<'EOF'
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

            EOF;
        $gitUnifiedOutput = Str::toSystemLineReturn($gitUnifiedOutput);

        $expectedUnifiedReturn = <<<'EOF'
            diff --git a/tests/FooTest.php b/tests/FooTest.php
            @@ -73 +73 @@ final class FooTest
            diff --git a/Bar.php b/Bar.php
            @@ -10,0 +11,3 @@ final class Bar
            @@ -21 +31,4 @@ final class Bar

            EOF;
        $expectedUnifiedReturn = Str::toSystemLineReturn($expectedUnifiedReturn);

        $shellCommandLineExecutor->expects($this->any())
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

        $diffProvider = new GitDiffFileProvider($shellCommandLineExecutor);
        $filter = $diffProvider->provideWithLines('master');

        $this->assertSame($expectedUnifiedReturn, $filter);
    }

    public function test_it_provides_the_infections_own_git_default_base(): void
    {
        $diffProvider = new GitDiffFileProvider(new ShellCommandLineExecutor());
        $this->assertSame('origin/master', $diffProvider->provideDefaultBase());
    }

    #[DataProvider('provideGitDefaultBaseExecutions')]
    public function test_it_provides_the_fallback_when_no_origin_upstream_defined(string $expectedBase, string $executorReturn): void
    {
        $shellCommandLineExecutor = $this->createMock(ShellCommandLineExecutor::class);
        $shellCommandLineExecutor->expects($this->any())
            ->method('execute')
            ->willReturn($executorReturn);

        $diffProvider = new GitDiffFileProvider($shellCommandLineExecutor);
        $this->assertSame($expectedBase, $diffProvider->provideDefaultBase());
    }

    public static function provideGitDefaultBaseExecutions(): iterable
    {
        yield ['origin/master', ''];

        yield ['origin/master', 'something/unexpected'];
    }

    public function test_it_provides_the_fallback_when_executor_throws(): void
    {
        $shellCommandLineExecutor = $this->createMock(ShellCommandLineExecutor::class);
        $shellCommandLineExecutor->expects($this->any())
            ->method('execute')
            ->willThrowException(new RuntimeException('ref refs/remotes/origin/HEAD is not a symbolic ref'));

        $diffProvider = new GitDiffFileProvider($shellCommandLineExecutor);
        $this->assertSame('origin/master', $diffProvider->provideDefaultBase());
    }
}
