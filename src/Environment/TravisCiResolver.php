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

use function Safe\sprintf;

/**
 * @internal
 *
 * @see https://docs.travis-ci.com/user/environment-variables/#default-environment-variables
 */
final class TravisCiResolver implements BuildContextResolver
{
    public function resolve(array $environment): BuildContext
    {
        if (
            !array_key_exists('TRAVIS', $environment)
            || !array_key_exists('TRAVIS_PULL_REQUEST', $environment)
            || $environment['TRAVIS'] !== 'true'
        ) {
            throw CouldNotResolveBuildContext::create('it is not a Travis CI');
        }

        if ($environment['TRAVIS_PULL_REQUEST'] !== 'false') {
            throw CouldNotResolveBuildContext::create(sprintf(
                'build is for a pull request (TRAVIS_PULL_REQUEST=%s)',
                $environment['TRAVIS_PULL_REQUEST']
            ));
        }

        if (
            !array_key_exists('TRAVIS_REPO_SLUG', $environment)
            || !array_key_exists('TRAVIS_BRANCH', $environment)
        ) {
            throw CouldNotResolveBuildContext::create('repository slug nor current branch were found; not a Travis build?');
        }

        return new BuildContext(
            $environment['TRAVIS_REPO_SLUG'],
            $environment['TRAVIS_BRANCH']
        );
    }
}
