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

namespace Infection\Tests\Configuration\ProjectDirectoryProvider;

use Infection\Configuration\ProjectDirectoryProvider\GitProjectDirectoryProvider;
use Infection\Git\Git;
use Infection\Git\NoGitProjectFound;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Psr\Log\Test\TestLogger;

#[CoversClass(GitProjectDirectoryProvider::class)]
final class GitProjectDirectoryProviderTest extends TestCase
{
    private Git&MockObject $gitMock;

    private TestLogger $logger;

    private GitProjectDirectoryProvider $provider;

    protected function setUp(): void
    {
        $this->gitMock = $this->createMock(Git::class);
        $this->logger = new TestLogger();

        $this->provider = new GitProjectDirectoryProvider(
            $this->gitMock,
            $this->logger,
        );
    }

    public function test_it_provides_the_project_directory(): void
    {
        $expected = '/path/to/project/directory';

        $this->gitMock
            ->expects($this->once())
            ->method('getProjectDirectory')
            ->willReturn($expected);

        $actual = $this->provider->provide();

        $this->assertSame($expected, $actual);
        $this->assertSame([], $this->logger->records);
    }

    public function test_it_returns_null_and_logs_when_no_git_project_is_found(): void
    {
        $exception = NoGitProjectFound::create(null);

        $this->gitMock
            ->expects($this->once())
            ->method('getProjectDirectory')
            ->willThrowException($exception);

        $actual = $this->provider->provide();

        $this->assertNull($actual);
        $this->assertEquals(
            [
                [
                    'level' => LogLevel::INFO,
                    'message' => 'Could not determine the project directory from Git.',
                    'context' => ['exception' => $exception],
                ],
            ],
            $this->logger->records,
        );
    }
}
