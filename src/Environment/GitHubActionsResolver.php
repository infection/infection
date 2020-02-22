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

namespace Infection\Environment;

use function array_key_exists;
use Safe\Exceptions\JsonException;
use function Safe\json_decode;
use function Safe\preg_match;
use function Safe\sprintf;

/**
 * @internal
 *
 * @see https://help.github.com/en/actions/configuring-and-managing-workflows/using-environment-variables#default-environment-variables
 */
final class GitHubActionsResolver implements BuildContextResolver
{
    public function resolve(array $environment): BuildContext
    {
        if (!array_key_exists('GITHUB_ACTIONS', $environment)) {
            throw new CouldNotResolveBuildContext('The environment variable "GITHUB_ACTIONS" is not present, so the build context does not appear to be a GitHub Actions workflow run.');
        }

        if ($environment['GITHUB_ACTIONS'] !== 'true') {
            throw new CouldNotResolveBuildContext(sprintf(
                'The value of the "GITHUB_ACTIONS" environment variable is expected to be "true", but it is "%s", so the build context does not appear to be a GitHub Actions workflow run.',
                $environment['GITHUB_ACTIONS']
            ));
        }

        if (!array_key_exists('GITHUB_CONTEXT', $environment)) {
            throw new CouldNotResolveBuildContext('The environment variable "GITHUB_CONTEXT" is not present, so the build context does not appear to be a GitHub Actions workflow run.');
        }

        try {
            $decoded = json_decode(
                $environment['GITHUB_CONTEXT'],
                true
            );
        } catch (JsonException $exception) {
            throw new CouldNotResolveBuildContext('The value of the "GITHUB_CONTEXT environment variable is expected to be a JSON object, but it is not, so the build context does not appear to be a GitHub Actions workflow run.');
        }

        if (!is_array($decoded)) {
            throw new CouldNotResolveBuildContext('The value of the "GITHUB_CONTEXT environment variable is expected to be a JSON object, but it is not, so the build context does not appear to be a GitHub Actions workflow run.');
        }

        if (!array_key_exists('repository', $decoded)) {
            throw new CouldNotResolveBuildContext('The value of the "GITHUB_CONTEXT" environment variable is expected to be a JSON object with a property "repository", but the property is missing, so the build context does not appear to be a GitHub Actions workflow run.');
        }

        if (!is_string($decoded['repository'])) {
            throw new CouldNotResolveBuildContext('The value of the "GITHUB_CONTEXT" environment variable is expected to be a JSON object with a property "repository" that is a string, but it is not, so the build context does not appear to be a GitHub Actions workflow run.');
        }

        $repositorySlug = $decoded['repository'];

        if (!array_key_exists('ref', $decoded)) {
            throw new CouldNotResolveBuildContext('The value of the "GITHUB_CONTEXT" environment variable is expected to be a JSON object with a property "ref", but the property is missing, so the build context does not appear to be a GitHub Actions workflow run.');
        }

        if (!is_string($decoded['ref'])) {
            throw new CouldNotResolveBuildContext('The value of the "GITHUB_CONTEXT" environment variable is expected to be a JSON object with a property "ref" that is a string, but it is not, so the build context does not appear to be a GitHub Actions workflow run.');
        }

        $pattern = '/^(?P<prefix>refs\/heads\/)(?P<branch>.+)$/';

        if (preg_match($pattern, $decoded['ref'], $matches) !== 1) {
            throw new CouldNotResolveBuildContext('The GitHub Actions workflow run does not appear to be for a branch.');
        }

        $branch = $matches['branch'];

        return new BuildContext(
            $repositorySlug,
            $branch
        );
    }
}
