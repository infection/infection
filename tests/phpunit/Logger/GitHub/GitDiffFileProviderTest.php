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
use PHPUnit\Framework\TestCase;

final class GitDiffFileProviderTest extends TestCase
{
    public function test_it_throws_no_code_to_mutate_exception_when_diff_is_empty(): void
    {
        $shellCommandLineExecutor = $this->createMock(ShellCommandLineExecutor::class);
        $shellCommandLineExecutor->expects($this->once())
            ->method('execute')
            ->willReturn('');

        $this->expectException(NoFilesInDiffToMutate::class);

        $diffProvider = new GitDiffFileProvider($shellCommandLineExecutor);
        $diffProvider->provide('AM', 'master');
    }

    public function test_it_executes_diff_and_returns_filter_as_a_string(): void
    {
        $shellCommandLineExecutor = $this->createMock(ShellCommandLineExecutor::class);
        $shellCommandLineExecutor->expects($this->once())
            ->method('execute')
            ->with('git diff \'master\' --diff-filter=\'AM\' --name-only | grep src/ | paste -s -d "," -')
            ->willReturn('src/A.php,src/B.php');

        $diffProvider = new GitDiffFileProvider($shellCommandLineExecutor);
        $filter = $diffProvider->provide('AM', 'master');

        $this->assertSame('src/A.php,src/B.php', $filter);
    }
}
