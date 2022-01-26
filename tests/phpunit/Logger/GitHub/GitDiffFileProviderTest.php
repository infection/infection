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

use Infection\Logger\GitHub\GitDiffFileProvider;
use Infection\Logger\GitHub\NoFilesInDiffToMutate;
use Infection\Process\ShellCommandLineExecutor;
use const PHP_OS_FAMILY;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;

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
        $diffProvider->provide('AM', 'master');
    }

    public function test_it_executes_diff_and_returns_filter_as_a_string(): void
    {
        $expectedMergeBaseCommandLine = 'git merge-base \'master\' HEAD';
        $expectedDiffCommandLine = 'git diff \'0ABCMERGE_BASE_342\' --diff-filter=\'AM\' --name-only | grep src/ | paste -s -d "," -';

        if (PHP_OS_FAMILY === 'Windows') {
            $expectedMergeBaseCommandLine = 'git merge-base "master" HEAD';
            $expectedDiffCommandLine = 'git diff "0ABCMERGE_BASE_342" --diff-filter="AM" --name-only | grep src/ | paste -s -d "," -';
        }

        $shellCommandLineExecutor = $this->createMock(ShellCommandLineExecutor::class);

        $shellCommandLineExecutor->expects($this->any())
            ->method('execute')
            ->willReturnCallback(function (string $command) use ($expectedDiffCommandLine, $expectedMergeBaseCommandLine): string {
                switch ($command) {
                    case $expectedMergeBaseCommandLine:
                        return "0ABCMERGE_BASE_342\n";
                    case $expectedDiffCommandLine:
                        return 'src/A.php,src/B.php';
                    default:
                        $this->fail("Unexpected shell command: $command");
                }
            });

        $diffProvider = new GitDiffFileProvider($shellCommandLineExecutor);
        $filter = $diffProvider->provide('AM', 'master');

        $this->assertSame('src/A.php,src/B.php', $filter);
    }

    public function test_it_falls_back_to_direct_diff_if_merge_base_is_not_availabe(): void
    {
        $expectedMergeBaseCommandLine = 'git merge-base \'master\' HEAD';
        $shellCommandLineExecutor = $this->createMock(ShellCommandLineExecutor::class);
        $expectedDiffCommandLine = 'git diff \'master\' --diff-filter=\'AM\' --name-only | grep src/ | paste -s -d "," -';

        $shellCommandLineExecutor->expects($this->any())
            ->method('execute')
            ->willReturnCallback(function (string $command) use ($expectedDiffCommandLine, $expectedMergeBaseCommandLine): string {
                switch ($command) {
                    case $expectedMergeBaseCommandLine:
                        throw $this->createStub(ProcessFailedException::class);
                    case $expectedDiffCommandLine:
                        return 'src/A.php,src/B.php';
                    default:
                        $this->fail("Unexpected shell command: $command");
                }
            });

        $diffProvider = new GitDiffFileProvider($shellCommandLineExecutor);
        $filter = $diffProvider->provide('AM', 'master');

        $this->assertSame('src/A.php,src/B.php', $filter);
    }

    public function test_it_provides_lines_filter_as_a_string(): void
    {
        $expectedMergeBaseCommandLine = 'git merge-base \'master\' HEAD';
        $expectedDiffCommandLine = 'git diff \'0ABCMERGE_BASE_342\' --unified=0 --diff-filter=AM | grep -v -e \'^[+-]\' -e \'^index\'';

        if (PHP_OS_FAMILY === 'Windows') {
            $expectedMergeBaseCommandLine = 'git merge-base "master" HEAD';
            $expectedDiffCommandLine = 'git diff "0ABCMERGE_BASE_342" --unified=0 --diff-filter=AM | grep -v -e \'^[+-]\' -e \'^index\'';
        }

        $shellCommandLineExecutor = $this->createMock(ShellCommandLineExecutor::class);

        $shellCommandLineExecutor->expects($this->any())
            ->method('execute')
            ->willReturnCallback(function (string $command) use ($expectedDiffCommandLine, $expectedMergeBaseCommandLine): string {
                switch ($command) {
                    case $expectedMergeBaseCommandLine:
                        return '0ABCMERGE_BASE_342';
                    case $expectedDiffCommandLine:
                        return '<LINE BY LINE GIT DIFF>';
                    default:
                        $this->fail("Unexpected shell command: $command");
                }
            });

        $diffProvider = new GitDiffFileProvider($shellCommandLineExecutor);
        $filter = $diffProvider->provideWithLines('master');

        $this->assertSame('<LINE BY LINE GIT DIFF>', $filter);
    }
}
