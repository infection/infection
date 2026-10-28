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

namespace Infection\Command\Option;

use Infection\CannotBeInstantiated;
use Infection\Configuration\SourceFilter\DiffFileFilter;
use Infection\Configuration\SourceFilter\IncompleteGitDiffFilter;
use Infection\Configuration\SourceFilter\PlainFilter;
use Infection\Configuration\SourceFilter\PositionalPathsFilter;
use Infection\Console\IO;
use Infection\Container\Container;
use Infection\Git\Git;
use function sprintf;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use function trim;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class SourceFilterOptions
{
    use CannotBeInstantiated;

    public const string PLAIN_FILTER_NAME = 'filter';

    private const string GIT_DIFF_FILTER_NAME = 'git-diff-filter';

    private const string GIT_DIFF_LINES_NAME = 'git-diff-lines';

    private const string GIT_DIFF_BASE_NAME = 'git-diff-base';

    private const string DIFF_FILE_NAME = 'diff-file';

    /**
     * @template T of Command
     * @param T $command
     *
     * @return T
     */
    public static function addOption(Command $command): Command
    {
        return $command
            ->addOption(
                self::PLAIN_FILTER_NAME,
                null,
                InputOption::VALUE_REQUIRED,
                'Filter which files to mutate. For example "src/Service/Mailer.php,src/Entity/Foobar.php".',
                Container::DEFAULT_FILTER,
            )
            ->addOption(
                self::GIT_DIFF_FILTER_NAME,
                null,
                InputOption::VALUE_REQUIRED,
                'Filter files to mutate by git <comment>"--diff-filter"</comment> option. <comment>A</comment> - only for added files, <comment>AM</comment> - for added and modified.',
                Container::DEFAULT_GIT_DIFF_FILTER,
            )
            ->addOption(
                self::GIT_DIFF_LINES_NAME,
                null,
                InputOption::VALUE_NONE,
                sprintf(
                    'Mutates only added and modified <comment>lines</comment> in files (applies the git diff filter "%s").',
                    Git::DEFAULT_GIT_DIFF_FILTER,
                ),
                Container::DEFAULT_GIT_DIFF_FILTER,
            )
            ->addOption(
                self::GIT_DIFF_BASE_NAME,
                null,
                InputOption::VALUE_REQUIRED,
                sprintf('Base for <comment>"--%1$s"</comment> option. Can be the git branch short name, full name or a commit hash. Must be used only together with <comment>"--%1$s"</comment>.', self::GIT_DIFF_FILTER_NAME),
                Container::DEFAULT_GIT_DIFF_BASE,
            )
            ->addOption(
                self::DIFF_FILE_NAME,
                null,
                InputOption::VALUE_REQUIRED,
                'Mutates only the lines added or modified by the given unified diff file. The diff must have no context lines, e.g. the output of <comment>"git diff --unified=0"</comment>. The file path and the paths in the diff are relative to the configuration file directory.',
            )
        ;
    }

    /**
     * @param list<non-empty-string> $positionalPaths
     */
    public static function get(IO $io, array $positionalPaths = []): PlainFilter|IncompleteGitDiffFilter|DiffFileFilter|PositionalPathsFilter|null
    {
        $input = $io->getInput();

        $filter = self::getPlainFilter($io);
        $diffFileFilter = self::getDiffFileFilter($input);

        // Checked before getGitFilter(), whose own check would otherwise tell a "--diff-file" user to add "--git-diff-lines" or "--git-diff-filter".
        self::assertDiffFileIsUsedAlone($diffFileFilter, $filter, $input, $positionalPaths);

        $gitFilter = self::getGitFilter($input);

        self::assertOnlyOneTypeOfFiltering($filter, $gitFilter, $positionalPaths);

        if ($diffFileFilter !== null) {
            return $diffFileFilter;
        }

        if ($positionalPaths !== []) {
            return new PositionalPathsFilter($positionalPaths);
        }

        return $filter ?? $gitFilter;
    }

    private static function getPlainFilter(IO $io): ?PlainFilter
    {
        $value = trim((string) $io->getInput()->getOption(self::PLAIN_FILTER_NAME));

        if ($value !== '') {
            $io->warning(
                'The "--filter" option is deprecated since 0.34.0 and will be removed in future versions. Use positional arguments instead: infection <filter>',
            );
        }

        return PlainFilter::tryToCreate($value);
    }

    private static function getGitFilter(InputInterface $input): ?IncompleteGitDiffFilter
    {
        $gitDiffFilter = self::getGitDiffFilter($input);

        $isForGitDiffLines = (bool) $input->getOption(self::GIT_DIFF_LINES_NAME);
        $gitDiffBase = self::getGitDiffBase($input);

        self::assertOnlyOneTypeOfGitFiltering($gitDiffFilter, $isForGitDiffLines);

        if ($isForGitDiffLines) {
            $gitDiffFilter = Git::DEFAULT_GIT_DIFF_FILTER;
        }

        self::assertGitBaseHasRequiredFilter($gitDiffFilter, $gitDiffBase);

        return $gitDiffFilter !== null
            ? new IncompleteGitDiffFilter($gitDiffFilter, $gitDiffBase)
            : null;
    }

    private static function getDiffFileFilter(InputInterface $input): ?DiffFileFilter
    {
        /** @var string|null $value */
        $value = $input->getOption(self::DIFF_FILE_NAME);

        if ($value === null) {
            return null;
        }

        $trimmedValue = trim($value);

        Assert::stringNotEmpty(
            $trimmedValue,
            sprintf(
                'Expected a non-blank value for the option "--%s".',
                self::DIFF_FILE_NAME,
            ),
        );

        return new DiffFileFilter($trimmedValue);
    }

    /**
     * @return non-empty-string|null
     */
    private static function getGitDiffFilter(InputInterface $input): ?string
    {
        $value = $input->getOption(self::GIT_DIFF_FILTER_NAME);

        if ($value === null) {
            return null;
        }

        $trimmedValue = trim((string) $value);

        Assert::stringNotEmpty(
            $trimmedValue,
            sprintf(
                'Expected a non-blank value for the option "--%s".',
                self::GIT_DIFF_FILTER_NAME,
            ),
        );

        return $trimmedValue;
    }

    /**
     * @return non-empty-string|null
     */
    private static function getGitDiffBase(InputInterface $input): ?string
    {
        $value = $input->getOption(self::GIT_DIFF_BASE_NAME);

        if ($value === null) {
            return null;
        }

        $trimmedValue = trim((string) $value);

        Assert::stringNotEmpty(
            $trimmedValue,
            sprintf(
                'Expected a non-blank value for the option "--%s".',
                self::GIT_DIFF_BASE_NAME,
            ),
        );

        return $trimmedValue;
    }

    private static function assertOnlyOneTypeOfGitFiltering(
        ?string $gitDiffFilter,
        bool $isForGitDiffLines,
    ): void {
        Assert::false(
            $isForGitDiffLines && $gitDiffFilter !== Container::DEFAULT_GIT_DIFF_FILTER,
            sprintf(
                'The options "--%s" and "--%s" are mutually exclusive. Please use only one of them.',
                self::GIT_DIFF_LINES_NAME,
                self::GIT_DIFF_FILTER_NAME,
            ),
        );
    }

    /**
     * @param non-empty-string|null $gitDiffFilter
     * @param non-empty-string|null $gitDiffBase
     */
    private static function assertGitBaseHasRequiredFilter(
        ?string $gitDiffFilter,
        ?string $gitDiffBase,
    ): void {
        Assert::false(
            $gitDiffBase !== null && $gitDiffFilter === null,
            sprintf(
                'The option "--%s" cannot be used without the option "--%s" or "--%s".',
                self::GIT_DIFF_BASE_NAME,
                self::GIT_DIFF_LINES_NAME,
                self::GIT_DIFF_FILTER_NAME,
            ),
        );
    }

    /**
     * @param list<non-empty-string> $positionalPaths
     */
    private static function assertOnlyOneTypeOfFiltering(
        ?PlainFilter $plainFilter,
        ?IncompleteGitDiffFilter $gitFilter,
        array $positionalPaths,
    ): void {
        Assert::false(
            $plainFilter !== null && $gitFilter !== null,
            sprintf(
                'The options "--%s" and "--%s" are mutually exclusive. Use "--%s" for regular filtering or "--%s" for Git-based filtering.',
                self::PLAIN_FILTER_NAME,
                self::GIT_DIFF_FILTER_NAME,
                self::PLAIN_FILTER_NAME,
                self::GIT_DIFF_FILTER_NAME,
            ),
        );

        Assert::false(
            $positionalPaths !== [] && $plainFilter !== null,
            sprintf(
                'Cannot pass source paths as positional arguments together with the "--%s" option. Use either form, not both.',
                self::PLAIN_FILTER_NAME,
            ),
        );

        Assert::false(
            $positionalPaths !== [] && $gitFilter !== null,
            sprintf(
                'Cannot pass positional paths together with "--%s" / "--%s". Use either form, not both.',
                self::GIT_DIFF_FILTER_NAME,
                self::GIT_DIFF_LINES_NAME,
            ),
        );
    }

    /**
     * @param list<non-empty-string> $positionalPaths
     */
    private static function assertDiffFileIsUsedAlone(
        ?DiffFileFilter $diffFileFilter,
        ?PlainFilter $plainFilter,
        InputInterface $input,
        array $positionalPaths,
    ): void {
        Assert::false(
            $diffFileFilter !== null && (
                $plainFilter !== null
                || $positionalPaths !== []
                || $input->getOption(self::GIT_DIFF_FILTER_NAME) !== null
                || $input->getOption(self::GIT_DIFF_LINES_NAME) === true
                || $input->getOption(self::GIT_DIFF_BASE_NAME) !== null
            ),
            sprintf(
                'The option "--%s" cannot be used together with source paths or the options "--%s", "--%s", "--%s" and "--%s".',
                self::DIFF_FILE_NAME,
                self::PLAIN_FILTER_NAME,
                self::GIT_DIFF_FILTER_NAME,
                self::GIT_DIFF_LINES_NAME,
                self::GIT_DIFF_BASE_NAME,
            ),
        );
    }
}
