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

use Composer\InstalledVersions;
use function count;
use function file_exists;
use const GLOB_ONLYDIR;
use function implode;
use Infection\Config\ConsoleHelper;
use Infection\Config\Guesser\SourceDirGuesser;
use Infection\Config\ValueProvider\ExcludeDirsProvider;
use Infection\Config\ValueProvider\PhpUnitCustomExecutablePathProvider;
use Infection\Config\ValueProvider\SourceDirsProvider;
use Infection\Config\ValueProvider\TestFrameworkConfigPathProvider;
use Infection\Config\ValueProvider\TextLogFileProvider;
use Infection\Configuration\Schema\SchemaConfigurationLoader;
use Infection\Console\Application;
use Infection\Console\IO;
use Infection\FileSystem\Finder\TestFrameworkFinder;
use Infection\TestFramework\Config\TestFrameworkConfigLocator;
use Infection\TestFramework\TestFrameworkTypes;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use OutOfBoundsException;
use RuntimeException;
use function Safe\file_get_contents;
use function Safe\file_put_contents;
use function Safe\glob;
use function Safe\json_decode;
use function Safe\json_encode;
use function sprintf;
use stdClass;
use function str_starts_with;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputOption;

/**
 * @internal
 */
final class ConfigureCommand extends BaseCommand
{
    public const NONINTERACTIVE_MODE_ERROR = 'Infection config generator requires an interactive mode.';

    /** @var string */
    private const OPTION_TEST_FRAMEWORK = 'test-framework';

    protected function configure(): void
    {
        $this
            ->setName('configure')
            ->setDescription('Create Infection config')
            ->addOption(
                self::OPTION_TEST_FRAMEWORK,
                null,
                InputOption::VALUE_REQUIRED,
                sprintf(
                    'Name of the Test framework to use ("%s")',
                    implode('", "', TestFrameworkTypes::getTypes()),
                ),
                TestFrameworkTypes::PHPUNIT,
            );
    }

    protected function executeCommand(IO $io): bool
    {
        if (!$io->isInteractive()) {
            $io->writeln(self::NONINTERACTIVE_MODE_ERROR);

            $this->abort();
        }

        /** @var FormatterHelper $formatterHelper */
        $formatterHelper = $this->getHelper('formatter');

        $consoleHelper = new ConsoleHelper($formatterHelper);
        $consoleHelper->writeSection(
            $io->getOutput(),
            'Welcome to the Infection config generator',
        );

        $io->newLine();
        $io->writeln('We did not find a configuration file. The following questions will help us to generate it for you.');
        $io->newLine();

        $dirsInCurrentDir = glob('*', GLOB_ONLYDIR);
        $testFrameworkConfigLocator = new TestFrameworkConfigLocator('.');

        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');

        if (file_exists('composer.json')) {
            $content = json_decode(file_get_contents('composer.json'));

            $sourceDirGuesser = new SourceDirGuesser($content);
        } else {
            $sourceDirGuesser = new SourceDirGuesser(new stdClass());
        }

        $sourceDirsProvider = new SourceDirsProvider($consoleHelper, $questionHelper, $sourceDirGuesser);
        $sourceDirs = $sourceDirsProvider->get($io, $dirsInCurrentDir);

        if (count($sourceDirs) === 0) {
            $io->writeln('A source directory was not provided. Unable to generate "infection.json.dist".');

            $this->abort();
        }

        $fileSystem = $this->getApplication()->getContainer()->getFileSystem();

        $excludeDirsProvider = new ExcludeDirsProvider(
            $consoleHelper,
            $questionHelper,
            $fileSystem,
        );

        $excludedDirs = $excludeDirsProvider->get($io, $dirsInCurrentDir, $sourceDirs);

        $phpUnitConfigPathProvider = new TestFrameworkConfigPathProvider($testFrameworkConfigLocator, $consoleHelper, $questionHelper);
        $phpUnitConfigPath = $phpUnitConfigPathProvider->get(
            $io,
            $dirsInCurrentDir,
            $io->getInput()->getOption(self::OPTION_TEST_FRAMEWORK),
        );

        $phpUnitExecutableFinder = new TestFrameworkFinder();
        $phpUnitCustomExecutablePathProvider = new PhpUnitCustomExecutablePathProvider($phpUnitExecutableFinder, $consoleHelper, $questionHelper);
        $phpUnitCustomExecutablePath = $phpUnitCustomExecutablePathProvider->get($io);

        $textLogFileProvider = new TextLogFileProvider($consoleHelper, $questionHelper);
        $textLogFilePath = $textLogFileProvider->get($io, $dirsInCurrentDir);

        $this->saveConfig($sourceDirs, $excludedDirs, $phpUnitConfigPath, $phpUnitCustomExecutablePath, $textLogFilePath);

        $io->newLine();
        $io->writeln(sprintf(
            'Configuration file "<comment>%s</comment>" was created.',
            SchemaConfigurationLoader::DEFAULT_JSON5_CONFIG_FILE,
        ));
        $io->newLine();

        return true;
    }

    /**
     * @param string[] $sourceDirs
     * @param string[] $excludedDirs
     */
    private function saveConfig(
        array $sourceDirs,
        array $excludedDirs,
        ?string $phpUnitConfigPath = null,
        ?string $phpUnitCustomExecutablePath = null,
        ?string $textLogFilePath = null,
    ): void {
        $configObject = new stdClass();

        $configObject->{'$schema'} = $this->getJsonSchemaPathOrUrl();

        $configObject->source = new stdClass();

        if ($sourceDirs !== []) {
            $configObject->source->directories = $sourceDirs;
        }

        if ($excludedDirs !== []) {
            $configObject->source->excludes = $excludedDirs;
        }

        if ($phpUnitConfigPath !== null) {
            $configObject->phpUnit = new stdClass();
            $configObject->phpUnit->configDir = $phpUnitConfigPath;
        }

        if ($phpUnitCustomExecutablePath !== null) {
            if (!isset($configObject->phpUnit)) {
                $configObject->phpUnit = new stdClass();
            }

            $configObject->phpUnit->customPath = $phpUnitCustomExecutablePath;
        }

        if ($textLogFilePath !== null) {
            $configObject->logs = new stdClass();
            $configObject->logs->text = $textLogFilePath;
        }

        /*
         * Explicitly add the default profile to the list of mutators, as even if it is
         * empty by default, it is not. If it would actually contain the default profile
         * by default, it would make the profiles feature more obvious to configure.
         */
        $configObject->mutators = [
            '@default' => true,
        ];

        file_put_contents(
            SchemaConfigurationLoader::DEFAULT_JSON5_CONFIG_FILE,
            json_encode($configObject, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        );
    }

    private function abort(): never
    {
        throw new RuntimeException('Configuration generation aborted');
    }

    private function getJsonSchemaPathOrUrl(): string
    {
        $fileName = 'vendor/infection/infection/resources/schema.json';

        if (file_exists($fileName)) {
            return $fileName;
        }

        try {
            $version = InstalledVersions::getPrettyVersion(Application::PACKAGE_NAME);

            if ($version === null || str_starts_with($version, 'dev-')) {
                $version = 'master';
            }
        } catch (OutOfBoundsException) {
            $version = 'master';
        }

        return sprintf('https://raw.githubusercontent.com/infection/infection/%s/resources/schema.json', $version);
    }
}
