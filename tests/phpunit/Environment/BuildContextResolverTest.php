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

use Infection\Environment\BuildContextResolver;
use Infection\Environment\CouldNotResolveBuildContext;
use PHPUnit\Framework\TestCase;
use function Safe\sprintf;

/**
 * @covers \Infection\Environment\BuildContextResolver
 */
final class BuildContextResolverTest extends TestCase
{
    public function test_resolve_throws_when_travis_key_does_not_exist_in_environment(): void
    {
        $environment = [
            'TRAVIS_BRANCH' => 'fix/this',
            'TRAVIS_PULL_REQUEST' => 'false',
            'TRAVIS_REPO_SLUG' => 'foo/bar',
        ];

        $buildContextResolver = new BuildContextResolver();

        $this->expectException(CouldNotResolveBuildContext::class);
        $this->expectExceptionMessage('it is not a Travis CI');

        $buildContextResolver->resolve($environment);
    }

    public function test_resolve_throws_when_travis_key_is_not_true(): void
    {
        $environment = [
            'TRAVIS' => 'false',
            'TRAVIS_BRANCH' => 'fix/this',
            'TRAVIS_PULL_REQUEST' => 'false',
            'TRAVIS_REPO_SLUG' => 'foo/bar',
        ];

        $buildContextResolver = new BuildContextResolver();

        $this->expectException(CouldNotResolveBuildContext::class);
        $this->expectExceptionMessage('it is not a Travis CI');

        $buildContextResolver->resolve($environment);
    }

    public function test_resolve_throws_when_travis_pull_request_key_does_not_exist_in_environment(): void
    {
        $environment = [
            'TRAVIS' => 'true',
            'TRAVIS_BRANCH' => 'fix/this',
            'TRAVIS_REPO_SLUG' => 'foo/bar',
        ];

        $buildContextResolver = new BuildContextResolver();

        $this->expectException(CouldNotResolveBuildContext::class);
        $this->expectExceptionMessage('it is not a Travis CI');

        $buildContextResolver->resolve($environment);
    }

    public function test_resolve_throws_when_travis_pull_request_is_not_false(): void
    {
        $environment = [
            'TRAVIS' => 'true',
            'TRAVIS_BRANCH' => 'fix/this',
            'TRAVIS_PULL_REQUEST' => '9001',
            'TRAVIS_REPO_SLUG' => 'foo/bar',
        ];

        $buildContextResolver = new BuildContextResolver();

        $this->expectException(CouldNotResolveBuildContext::class);
        $this->expectExceptionMessage(sprintf(
            'build is for a pull request (TRAVIS_PULL_REQUEST=%s)',
            $environment['TRAVIS_PULL_REQUEST']
        ));

        $buildContextResolver->resolve($environment);
    }

    public function test_resolve_throws_when_travis_pull_repo_slug_key_does_not_exist(): void
    {
        $environment = [
            'TRAVIS' => 'true',
            'TRAVIS_BRANCH' => 'fix/this',
            'TRAVIS_PULL_REQUEST' => 'false',
        ];

        $buildContextResolver = new BuildContextResolver();

        $this->expectException(CouldNotResolveBuildContext::class);
        $this->expectExceptionMessage('repository slug nor current branch were found; not a Travis build?');

        $buildContextResolver->resolve($environment);
    }

    public function test_resolve_throws_when_travis_branch_key_does_not_exist(): void
    {
        $environment = [
            'TRAVIS' => 'true',
            'TRAVIS_PULL_REQUEST' => 'false',
            'TRAVIS_REPO_SLUG' => 'foo/bar',
        ];

        $buildContextResolver = new BuildContextResolver();

        $this->expectException(CouldNotResolveBuildContext::class);
        $this->expectExceptionMessage('repository slug nor current branch were found; not a Travis build?');

        $buildContextResolver->resolve($environment);
    }

    public function test_resolve_returns_build_context_when_environment_is_branch_build(): void
    {
        $environment = [
            'TRAVIS' => 'true',
            'TRAVIS_BRANCH' => 'fix/this',
            'TRAVIS_PULL_REQUEST' => 'false',
            'TRAVIS_REPO_SLUG' => 'foo/bar',
        ];

        $buildContextResolver = new BuildContextResolver();

        $buildContext = $buildContextResolver->resolve($environment);

        self::assertSame($environment['TRAVIS_REPO_SLUG'], $buildContext->repositorySlug());
        self::assertSame($environment['TRAVIS_BRANCH'], $buildContext->branch());
    }
}
