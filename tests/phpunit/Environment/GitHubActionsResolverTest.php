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
use Infection\Environment\CouldNotResolveBuildContext;
use Infection\Environment\GitHubActionsResolver;
use PHPUnit\Framework\TestCase;
use function Safe\json_encode;
use function Safe\sprintf;

final class GitHubActionsResolverTest extends TestCase
{
    public function test_resolve_throws_when_github_actions_key_does_not_exist_in_environment(): void
    {
        $environment = [
            'GITHUB_CONTEXT' => json_encode([
                'ref' => 'refs/heads/master',
                'repository' => 'foo/bar',
            ]),
        ];

        $buildContextResolver = new GitHubActionsResolver();

        $this->expectException(CouldNotResolveBuildContext::class);
        $this->expectExceptionMessage('The environment variable "GITHUB_ACTIONS" is not present, so the build context does not appear to be a GitHub Actions workflow run.');

        $buildContextResolver->resolve($environment);
    }

    public function test_resolve_throws_when_value_for_github_actions_key_does_is_not_true(): void
    {
        $environment = [
            'GITHUB_ACTIONS' => 'qux',
            'GITHUB_CONTEXT' => json_encode([
                'ref' => 'refs/heads/fix/this',
                'repository' => 'foo/bar',
            ]),
        ];

        $buildContextResolver = new GitHubActionsResolver();

        $this->expectException(CouldNotResolveBuildContext::class);
        $this->expectExceptionMessage(sprintf(
            'The value of the "GITHUB_ACTIONS" environment variable is expected to be "true", but it is "%s", so the build context does not appear to be a GitHub Actions workflow run.',
            $environment['GITHUB_ACTIONS']
        ));

        $buildContextResolver->resolve($environment);
    }

    public function test_resolve_throws_when_github_context_key_does_not_exist_in_environment(): void
    {
        $environment = [
            'GITHUB_ACTIONS' => 'true',
        ];

        $buildContextResolver = new GitHubActionsResolver();

        $this->expectException(CouldNotResolveBuildContext::class);
        $this->expectExceptionMessage('The environment variable "GITHUB_CONTEXT" is not present, so the build context does not appear to be a GitHub Actions workflow run.');

        $buildContextResolver->resolve($environment);
    }

    /**
     * @dataProvider provideGitHubContextThatIsNotAJsonObject
     */
    public function test_resolve_throws_when_value_for_github_context_key_is_not_a_json_object(string $githubContext): void
    {
        $environment = [
            'GITHUB_ACTIONS' => 'true',
            'GITHUB_CONTEXT' => $githubContext,
        ];

        $buildContextResolver = new GitHubActionsResolver();

        $this->expectException(CouldNotResolveBuildContext::class);
        $this->expectExceptionMessage('The value of the "GITHUB_CONTEXT environment variable is expected to be a JSON object, but it is not, so the build context does not appear to be a GitHub Actions workflow run.');

        $buildContextResolver->resolve($environment);
    }

    public function provideGitHubContextThatIsNotAJsonObject(): Generator
    {
        yield 'json-false' => ['"false"'];

        yield 'json-null' => ['"null"'];

        yield 'json-true' => ['"true"'];

        yield 'json-string' => ['"Hmmm"'];

        yield 'not-json' => ['Hmm'];
    }

    public function test_resolve_throws_when_value_for_github_context_does_not_have_key_for_repository(): void
    {
        $environment = [
            'GITHUB_ACTIONS' => 'true',
            'GITHUB_CONTEXT' => json_encode([
                'ref' => 'refs/heads/fix/this',
            ]),
        ];

        $buildContextResolver = new GitHubActionsResolver();

        $this->expectException(CouldNotResolveBuildContext::class);
        $this->expectExceptionMessage('The value of the "GITHUB_CONTEXT" environment variable is expected to be a JSON object with a property "repository", but the property is missing, so the build context does not appear to be a GitHub Actions workflow run.');

        $buildContextResolver->resolve($environment);
    }

    public function test_resolve_throws_when_value_for_property_repository_in_github_context_is_not_a_string(): void
    {
        $environment = [
            'GITHUB_ACTIONS' => 'true',
            'GITHUB_CONTEXT' => json_encode([
                'ref' => 'refs/heads/fix/this',
                'repository' => null,
            ]),
        ];

        $buildContextResolver = new GitHubActionsResolver();

        $this->expectException(CouldNotResolveBuildContext::class);
        $this->expectExceptionMessage('The value of the "GITHUB_CONTEXT" environment variable is expected to be a JSON object with a property "repository" that is a string, but it is not, so the build context does not appear to be a GitHub Actions workflow run.');

        $buildContextResolver->resolve($environment);
    }

    public function test_resolve_throws_when_value_for_github_context_does_not_have_key_for_ref(): void
    {
        $environment = [
            'GITHUB_ACTIONS' => 'true',
            'GITHUB_CONTEXT' => json_encode([
                'repository' => 'foo/bar',
            ]),
        ];

        $buildContextResolver = new GitHubActionsResolver();

        $this->expectException(CouldNotResolveBuildContext::class);
        $this->expectExceptionMessage('The value of the "GITHUB_CONTEXT" environment variable is expected to be a JSON object with a property "ref", but the property is missing, so the build context does not appear to be a GitHub Actions workflow run.');

        $buildContextResolver->resolve($environment);
    }

    public function test_resolve_throws_when_value_for_property_ref_in_github_context_is_not_a_string(): void
    {
        $environment = [
            'GITHUB_ACTIONS' => 'true',
            'GITHUB_CONTEXT' => json_encode([
                'ref' => null,
                'repository' => 'foo/bar',
            ]),
        ];

        $buildContextResolver = new GitHubActionsResolver();

        $this->expectException(CouldNotResolveBuildContext::class);
        $this->expectExceptionMessage('The value of the "GITHUB_CONTEXT" environment variable is expected to be a JSON object with a property "ref" that is a string, but it is not, so the build context does not appear to be a GitHub Actions workflow run.');

        $buildContextResolver->resolve($environment);
    }

    /**
     * @dataProvider provideRefThatIsNotABranch
     */
    public function test_resolve_throws_when_value_for_property_ref_in_github_context_does_not_reference_a_branch(string $ref): void
    {
        $environment = [
            'GITHUB_ACTIONS' => 'true',
            'GITHUB_CONTEXT' => json_encode([
                'ref' => $ref,
                'repository' => 'foo/bar',
            ]),
        ];

        $buildContextResolver = new GitHubActionsResolver();

        $this->expectException(CouldNotResolveBuildContext::class);
        $this->expectExceptionMessage('The GitHub Actions workflow run does not appear to be for a branch.');

        $buildContextResolver->resolve($environment);
    }

    public function provideRefThatIsNotABranch(): Generator
    {
        yield 'string-empty' => [''];

        yield 'string-blank' => [''];

        yield 'string-pull-request' => ['refs/pull/107/merge'];
    }

    /**
     * @dataProvider provideRefThatIsABranch
     */
    public function test_resolve_returns_build_context_when_ref_matches_branch(string $branch): void
    {
        $repositorySlug = 'foo/bar';

        $environment = [
            'GITHUB_ACTIONS' => 'true',
            'GITHUB_CONTEXT' => json_encode([
                'ref' => sprintf(
                    'refs/heads/%s',
                    $branch
                ),
                'repository' => $repositorySlug,
            ]),
        ];

        $buildContextResolver = new GitHubActionsResolver();

        $buildContext = $buildContextResolver->resolve($environment);

        $this->assertSame($branch, $buildContext->branch());
        $this->assertSame($repositorySlug, $buildContext->repositorySlug());
    }

    public function provideRefThatIsABranch(): Generator
    {
        yield 'string-dependabot' => ['dependabot/composer/foo/bar-1.2.3'];

        yield 'string-fix' => ['fix/this'];

        yield 'string-master' => ['master'];
    }
}
