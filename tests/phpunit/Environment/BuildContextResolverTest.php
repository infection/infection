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

namespace Infection\Tests\Environment;

use Generator;
use Infection\Environment\BuildContextResolver;
use Infection\Environment\CouldNotResolveBuildContext;
use OndraM\CiDetector\Ci\CiInterface;
use OndraM\CiDetector\CiDetector;
use OndraM\CiDetector\Exception\CiNotDetectedException;
use OndraM\CiDetector\TrinaryLogic;
use PHPUnit\Framework\TestCase;

final class BuildContextResolverTest extends TestCase
{
    public function test_resolve_throws_when_ci_could_not_be_detected(): void
    {
        $ciDetector = $this->createMock(CiDetector::class);

        $ciDetector
            ->method('detect')
            ->willThrowException(new CiNotDetectedException());

        $buildContextResolver = new BuildContextResolver($ciDetector);

        $this->expectException(CouldNotResolveBuildContext::class);
        $this->expectExceptionMessage('The current process is not executed in a CI build');

        $buildContextResolver->resolve();
    }

    public function test_resolve_throws_when_ci_is_in_pull_request_context(): void
    {
        $ci = $this->createMock(CiInterface::class);

        $ci
            ->method('isPullRequest')
            ->willReturn(TrinaryLogic::createFromBoolean(true));

        $ciDetector = $this->createMock(CiDetector::class);

        $ciDetector
            ->method('detect')
            ->willReturn($ci);

        $buildContextResolver = new BuildContextResolver($ciDetector);

        $this->expectException(CouldNotResolveBuildContext::class);
        $this->expectExceptionMessage('The current process is a pull request build');

        $buildContextResolver->resolve();
    }

    public function test_resolve_throws_when_ci_is_maybe_in_pull_request_context(): void
    {
        $ci = $this->createMock(CiInterface::class);

        $ci
            ->method('isPullRequest')
            ->willReturn(TrinaryLogic::createMaybe());

        $ciDetector = $this->createMock(CiDetector::class);

        $ciDetector
            ->method('detect')
            ->willReturn($ci);

        $buildContextResolver = new BuildContextResolver($ciDetector);

        $this->expectException(CouldNotResolveBuildContext::class);
        $this->expectExceptionMessage('The current process is maybe a pull request build');

        $buildContextResolver->resolve();
    }

    /**
     * @dataProvider provideBlankOrEmptyString
     */
    public function test_resolve_throws_when_repository_name_is_empty(string $repositoryName): void
    {
        $gitBranch = 'fix/this';

        $ci = $this->createMock(CiInterface::class);

        $ci
            ->method('isPullRequest')
            ->willReturn(TrinaryLogic::createFromBoolean(false));

        $ci
            ->method('getRepositoryName')
            ->willReturn($repositoryName);

        $ci
            ->method('getGitBranch')
            ->willReturn($gitBranch);

        $ciDetector = $this->createMock(CiDetector::class);

        $ciDetector
            ->method('detect')
            ->willReturn($ci);

        $buildContextResolver = new BuildContextResolver($ciDetector);

        $this->expectException(CouldNotResolveBuildContext::class);
        $this->expectExceptionMessage('The repository name could not be determined for the current process');

        $buildContextResolver->resolve();
    }

    /**
     * @dataProvider provideBlankOrEmptyString
     */
    public function test_resolve_throws_when_branch_name_is_empty(string $gitBranch): void
    {
        $repositoryName = 'foo/bar';

        $ci = $this->createMock(CiInterface::class);

        $ci
            ->method('isPullRequest')
            ->willReturn(TrinaryLogic::createFromBoolean(false));

        $ci
            ->method('getRepositoryName')
            ->willReturn($repositoryName);

        $ci
            ->method('getGitBranch')
            ->willReturn($gitBranch);

        $ciDetector = $this->createMock(CiDetector::class);

        $ciDetector
            ->method('detect')
            ->willReturn($ci);

        $buildContextResolver = new BuildContextResolver($ciDetector);

        $this->expectException(CouldNotResolveBuildContext::class);
        $this->expectExceptionMessage('The branch name could not be determined for the current process');

        $buildContextResolver->resolve();
    }

    public function provideBlankOrEmptyString(): Generator
    {
        yield 'string-blank' => [' '];

        yield 'string-empty' => [''];
    }

    public function test_resolve_returns_build_context_when_ci_is_detected_and_build_is_not_for_pull_request(): void
    {
        $repositoryName = 'foo/bar';
        $gitBranch = 'fix/this';

        $ci = $this->createMock(CiInterface::class);

        $ci
            ->method('isPullRequest')
            ->willReturn(TrinaryLogic::createFromBoolean(false));

        $ci
            ->method('getRepositoryName')
            ->willReturn($repositoryName);

        $ci
            ->method('getGitBranch')
            ->willReturn($gitBranch);

        $ciDetector = $this->createMock(CiDetector::class);

        $ciDetector
            ->method('detect')
            ->willReturn($ci);

        $buildContextResolver = new BuildContextResolver($ciDetector);

        $buildContext = $buildContextResolver->resolve();

        $this->assertSame($repositoryName, $buildContext->repositorySlug());
        $this->assertSame($gitBranch, $buildContext->branch());
    }
}
