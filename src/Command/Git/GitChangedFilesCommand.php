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

namespace Infection\Command\Git;

use function explode;
use Infection\Command\BaseCommand;
use Infection\Command\Git\Option\BaseOption;
use Infection\Command\Git\Option\FilterOption;
use Infection\Command\Option\ConfigurationOption;
use Infection\Configuration\SourceFilter\GitDiffFilter;
use Infection\Configuration\SourceFilter\IncompleteGitDiffFilter;
use Infection\Console\IO;
use function sprintf;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class GitChangedFilesCommand extends BaseCommand
{
    public function __construct()
    {
        parent::__construct('git:changed-files');
    }

    protected function configure(): void
    {
        $this->setDescription(
            'Finds the list of relative paths (relative to the current working directory) of the changed files that changed compared to the base branch used and matching the given filter.',
        );

        ConfigurationOption::addOption($this);
        BaseOption::addOption($this);
        FilterOption::addOption($this);
    }

    protected function executeCommand(IO $io): bool
    {
        $logger = LoggerFactory::create($io->getOutput());

        $inputBase = BaseOption::get($io);
        $inputFilter = FilterOption::get($io);

        $container = $this->getApplication()->getContainer()->withValues(
            logger: $logger,
            output: $io->getOutput(),
            configFile: ConfigurationOption::get($io),
            sourceFilter: new IncompleteGitDiffFilter($inputFilter, $inputBase),
        );

        $git = $container->getGit();

        if ($inputBase === null) {
            $logger->notice(
                sprintf(
                    'No base found. Using the default base "%s".',
                    $git->getDefaultBase(),
                ),
            );
        }

        $sourceFilter = $container->getConfiguration()->sourceFilter;
        Assert::isInstanceOf($sourceFilter, GitDiffFilter::class);

        $logger->notice(
            sprintf(
                'Using the reference "%s".',
                $sourceFilter->base,
            ),
        );

        $files = explode(
            ',',
            $git->getChangedFileRelativePaths(
                $sourceFilter->value,
                $sourceFilter->base,
                $container->getConfiguration()->source->directories,
            ),
        );

        $io->writeln($files);

        return true;
    }
}
