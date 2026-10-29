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

namespace Infection\Git;

use function getenv;
use Infection\FileSystem\FileSystem;
use function is_string;
use Override;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Safe\Exceptions\JsonException;
use function Safe\json_decode;
use function sprintf;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * @internal
 *
 * Uses the current request's comparison base from GitHub or GitLab CI metadata
 * when available and delegates other Git operations. Comparing only the request's
 * own changes also requires checking out its head and making the base and any
 * required history available.
 */
final readonly class CiAwareGit implements Git
{
    public function __construct(
        private Git $decoratedGit,
        private FileSystem $fileSystem,
        private LoggerInterface $logger = new NullLogger(),
    ) {
    }

    #[Override]
    public function getDefaultBase(): string
    {
        return $this->findCiBase() ?? $this->decoratedGit->getDefaultBase();
    }

    #[Override]
    public function getChangedFilePaths(
        string $diffFilter,
        string $base,
        array $sourceDirectories,
        string $workingDirectory,
    ): array {
        return $this->decoratedGit->getChangedFilePaths(
            $diffFilter,
            $base,
            $sourceDirectories,
            $workingDirectory,
        );
    }

    #[Override]
    public function getChangedLinesRangesByFilePaths(
        string $diffFilter,
        string $base,
        array $sourceDirectories,
        string $workingDirectory,
    ): array {
        return $this->decoratedGit->getChangedLinesRangesByFilePaths(
            $diffFilter,
            $base,
            $sourceDirectories,
            $workingDirectory,
        );
    }

    #[Override]
    public function getBaseReference(string $base): string
    {
        return $this->decoratedGit->getBaseReference($base);
    }

    #[Override]
    public function getProjectDirectory(): string
    {
        return $this->decoratedGit->getProjectDirectory();
    }

    /**
     * @return non-empty-string|null
     */
    private function findCiBase(): ?string
    {
        if (getenv('GITLAB_CI') === 'true') {
            // This is the merge base of the MR diff. Unlike the target branch
            // SHA, it is also available in detached merge request pipelines.
            return self::findNonEmptyString(
                getenv('CI_MERGE_REQUEST_DIFF_BASE_SHA'),
            );
        }

        if (getenv('GITHUB_ACTIONS') !== 'true') {
            return null;
        }

        $eventPath = getenv('GITHUB_EVENT_PATH');

        if ($eventPath === false || $eventPath === '') {
            return null;
        }

        try {
            return self::findGitHubBase(
                json_decode(
                    $this->fileSystem->readFile($eventPath),
                ),
            );
        } catch (IOException|JsonException $exception) {
            $this->logger->info(
                sprintf(
                    'Could not determine the GitHub pull request base from "%s". Falling back to the default Git base.',
                    $eventPath,
                ),
                ['exception' => $exception],
            );

            return null;
        }
    }

    /**
     * @return non-empty-string|null
     */
    private static function findGitHubBase(mixed $event): ?string
    {
        // GITHUB_BASE_REF can name the final base of a native stack. The event
        // payload records the immediate target of this pull request instead.
        return self::findNonEmptyString($event->pull_request->base->sha ?? null);
    }

    /**
     * @return non-empty-string|null
     */
    private static function findNonEmptyString(mixed $base): ?string
    {
        return is_string($base) && $base !== '' ? $base : null;
    }
}
