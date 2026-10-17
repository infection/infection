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

namespace Infection\Command;

use function array_map;
use Infection\Command\Git\LoggerFactory;
use Infection\Command\Option\ConfigurationOption;
use Infection\Command\Option\PathsArgument;
use Infection\Command\Option\SourceFilterOptions;
use Infection\Configuration\Configuration;
use Infection\Console\IO;
use Infection\Source\Collector\SourceCollector;
use Infection\Source\Exception\NoSourceFound;
use function Safe\getcwd;
use function sort;
use SplFileInfo;
use function sprintf;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Filesystem\Path;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class ListSourcesCommand extends BaseCommand
{
    public const string NAME = 'config:list-sources';

    public function __construct()
    {
        parent::__construct(self::NAME);
    }

    protected function configure(): void
    {
        PathsArgument::addArgument($this);

        ConfigurationOption::addOption($this);
        SourceFilterOptions::addOption($this);

        $this->setDescription(
            'Finds the paths of the collected source files.',
        );
    }

    protected function executeCommand(IO $io): bool
    {
        $diagnosticIo = self::createDiagnosticIo($io);

        $container = $this->getApplication()->getContainer()->withValues(
            logger: LoggerFactory::create($io->getOutput()),
            output: $io->getOutput(),
            configFile: ConfigurationOption::get($io),
            sourceFilter: SourceFilterOptions::get(
                $diagnosticIo,
                PathsArgument::get($io),
            ),
        );

        self::assertNoSymbolSelectors($container->getConfiguration());

        $filePaths = self::collectPaths(
            getcwd(),
            $container->getSourceCollector(),
        );

        $io->writeln($filePaths);

        return true;
    }

    // The --filter warning uses IO directly and bypasses the logger.
    // Route it to stderr here until diagnostic logging is centralized.
    private static function createDiagnosticIo(IO $io): IO
    {
        $output = $io->getOutput();
        $errorOutput = $output instanceof ConsoleOutputInterface
            ? $output->getErrorOutput()
            : $output;

        return new IO(
            $io->getInput(),
            $errorOutput,
        );
    }

    private static function assertNoSymbolSelectors(Configuration $configuration): void
    {
        if (
            $configuration->sourceFilter->filtersFiles()
            || $configuration->testFrameworkExtraOptions !== ''
        ) {
            return;
        }

        Assert::isEmpty(
            $configuration->sourceFilter->symbolSelectors,
            sprintf(
                'Source symbol selectors do not affect which source files are listed and cannot be used on their own with %s. They are accepted alongside source or test paths so the same arguments can be reused from the run command.',
                self::NAME,
            ),
        );
    }

    /**
     * @throws NoSourceFound
     *
     * @return string[]
     */
    private static function collectPaths(
        string $cwd,
        SourceCollector $sourceCollector,
    ): array {
        $paths = array_map(
            static fn (SplFileInfo $fileInfo) => Path::makeRelative(
                $fileInfo->getRealPath(),
                $cwd,
            ),
            $sourceCollector->collect(),
        );

        sort($paths);

        return $paths;
    }
}
