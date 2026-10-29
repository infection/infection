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

use function array_filter;
use function array_map;
use function array_merge;
use function count;
use function implode;
use Infection\Differ\ChangedLinesParser;
use Infection\Source\Exception\NoSourceFound;
use Infection\TestFramework\Contracts\ShellCommandRunner;
use const PHP_EOL;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use function Safe\preg_split;
use function sprintf;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Process\Exception\ExceptionInterface as ProcessException;
use Webmozart\Assert\Assert;

/**
 * @internal
 *
 * Implementation of the Git contract leveraging the git binary via processes.
 */
final readonly class CommandLineGit implements Git
{
    // https://github.com/infection/infection/issues/2611
    private const string DEFAULT_SYMBOLIC_REFERENCE = 'refs/remotes/origin/HEAD';

    public function __construct(
        private ShellCommandRunner $shellCommandRunner,
        private LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function getDefaultBase(): string
    {
        return $this->readSymbolicReference(self::DEFAULT_SYMBOLIC_REFERENCE) ?? Git::FALLBACK_BASE;
    }

    public function getChangedFilePaths(
        string $diffFilter,
        string $base,
        array $sourceDirectories,
        string $workingDirectory,
    ): array {
        [$projectDirectory, $lines] = $this->diff(
            $diffFilter,
            $base,
            $sourceDirectories,
            $workingDirectory,
            nameOnly: true,
        );
        $paths = self::makePathsAbsolute($lines, $projectDirectory);

        if (count($paths) === 0) {
            throw NoSourceFound::noFilesForGitDiff($diffFilter, $base);
        }

        return $paths;
    }

    public function getChangedLinesRangesByFilePaths(
        string $diffFilter,
        string $base,
        array $sourceDirectories,
        string $workingDirectory,
    ): array {
        [$projectDirectory, $lines] = $this->diff(
            $diffFilter,
            $base,
            $sourceDirectories,
            $workingDirectory,
            noContext: true,
        );
        $changedLines = ChangedLinesParser::parse($lines, $projectDirectory);

        if (count($changedLines) === 0) {
            throw NoSourceFound::noChangedLinesForGitDiff(
                $diffFilter,
                $base,
                implode(PHP_EOL, $lines),
            );
        }

        return $changedLines;
    }

    public function getBaseReference(string $base): string
    {
        try {
            $reference = $this->shellCommandRunner->mustRun([
                'git',
                'merge-base',
                $base,
                'HEAD',
            ]);

            Assert::stringNotEmpty($reference);

            return $reference;
        } catch (ProcessException $exception) {
            $this->logger->info(
                sprintf(
                    'Could not find a common ancestor commit between "%1$s" and "HEAD" and fell back to the base "%1$s". This can if there is no common ancestor commit or if we are in a shallow commit.',
                    $base,
                ),
                ['exception' => $exception],
            );
        }

        return $base;
    }

    public function getProjectDirectory(): string
    {
        // An error here should really not happen, so we are fine to let it
        // bubble up instead of throwing a dedicated exception or providing a
        // fallback.
        try {
            $directory = $this->shellCommandRunner->mustRun([
                'git',
                'rev-parse',
                '--show-toplevel',
            ]);
        } catch (ProcessException $exception) {
            throw NoGitProjectFound::create($exception);
        }

        Assert::stringNotEmpty($directory);

        return $directory;
    }

    /**
     * @param list<string> $paths
     *
     * @return list<non-empty-string>
     */
    private static function makePathsAbsolute(
        array $paths,
        string $projectDirectory,
    ): array {
        return array_map(
            static fn (string $path): string => self::makePathAbsolute($projectDirectory, $path),
            $paths,
        );
    }

    /**
     * @return non-empty-string
     */
    private static function makePathAbsolute(string $projectDirectory, string $path): string
    {
        $absolutePath = Path::join($projectDirectory, $path);
        Assert::stringNotEmpty($absolutePath);

        return $absolutePath;
    }

    /**
     * @param string[] $sourceDirectories
     *
     * @return array{string, list<string>}
     */
    private function diff(
        string $diffFilter,
        string $base,
        array $sourceDirectories,
        string $workingDirectory,
        bool $nameOnly = false,
        bool $noContext = false,
    ): array {
        $projectDirectory = $this->shellCommandRunner->mustRun([
            'git',
            '-C',
            $workingDirectory,
            'rev-parse',
            '--show-toplevel',
        ]);

        $command = [
            'git',
            '-C',
            $workingDirectory,
            '--no-pager',
            'diff',
            $base,
            '--no-ext-diff',
            '--no-color',
            $nameOnly ? '--name-only' : null,
            $noContext ? '--unified=0' : null,
            '--diff-filter=' . $diffFilter,
            '--',
        ];

        $diff = $this->shellCommandRunner->mustRun(
            array_merge(
                array_filter($command),
                $sourceDirectories,
            ),
        );

        if ($diff === '') {
            return [$projectDirectory, []];
        }

        return [
            $projectDirectory,
            preg_split('/\n|\r\n?/', $diff),
        ];
    }

    /**
     * @return non-empty-string|null
     */
    private function readSymbolicReference(string $name): ?string
    {
        // see https://www.reddit.com/r/git/comments/jbdb7j/comment/lpdk30e/
        try {
            $reference = $this->shellCommandRunner->mustRun([
                'git',
                'symbolic-ref',
                $name,
            ]);

            Assert::stringNotEmpty($reference);

            return $reference;
        } catch (ProcessException $exception) {
            $this->logger->info(
                sprintf(
                    'Could not find a symbolic reference for "%s".',
                    $name,
                ),
                ['exception' => $exception],
            );
        }

        return null;
    }
}
