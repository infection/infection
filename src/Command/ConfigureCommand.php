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

use Infection\Config\ConsoleHelper;
use Infection\Config\Guesser\SourceDirGuesser;
use Infection\Config\InfectionConfig;
use Infection\Config\ValueProvider\ExcludeDirsProvider;
use Infection\Config\ValueProvider\PhpUnitCustomExecutablePathProvider;
use Infection\Config\ValueProvider\SourceDirsProvider;
use Infection\Config\ValueProvider\TestFrameworkConfigPathProvider;
use Infection\Config\ValueProvider\TextLogFileProvider;
use Infection\Config\ValueProvider\TimeoutProvider;
use Infection\Finder\TestFrameworkFinder;
use Infection\TestFramework\Config\TestFrameworkConfigLocator;
use Infection\TestFramework\TestFrameworkTypes;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class ConfigureCommand extends BaseCommand
{
    public const NONINTERACTIVE_MODE_ERROR = 'Infection config generator requires an interactive mode.';

    protected function configure(): void
    {
        $this->setName('configure')
            ->setDescription('Create Infection config')
            ->addOption(
                'test-framework',
                null,
                InputOption::VALUE_REQUIRED,
                'Name of the Test framework to use (' . implode(', ', TestFrameworkTypes::TYPES) . ')',
                TestFrameworkTypes::PHPUNIT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->isInteractive()) {
            $output->writeln(self::NONINTERACTIVE_MODE_ERROR);

            return 1;
        }

        $consoleHelper = new ConsoleHelper($this->getHelper('formatter'));
        $consoleHelper->writeSection($output, 'Welcome to the Infection config generator');

        $output->writeln([
            '',
            'We did not find a configuration file. The following questions will help us to generate it for you.',
            '',
        ]);

        $dirsInCurrentDir = array_filter(glob('*'), 'is_dir');
        $testFrameworkConfigLocator = new TestFrameworkConfigLocator('.');

        $questionHelper = $this->getHelper('question');

        if (file_exists('composer.json')) {
            $content = file_get_contents('composer.json');
            \assert(\is_string($content));
            $content = json_decode($content);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \LogicException('composer.json does not contain valid JSON');
            }

            $sourceDirGuesser = new SourceDirGuesser($content);
        } else {
            $sourceDirGuesser = new SourceDirGuesser(new \stdClass());
        }

        $sourceDirsProvider = new SourceDirsProvider($consoleHelper, $questionHelper, $sourceDirGuesser);
        $sourceDirs = $sourceDirsProvider->get($input, $output, $dirsInCurrentDir);

        if (empty($sourceDirs)) {
            $output->writeln('A source directory was not provided. Unable to generate "infection.json.dist".');

            return 1;
        }

        $excludeDirsProvider = new ExcludeDirsProvider(
            $consoleHelper,
            $questionHelper,
            $this->getContainer()->get(Filesystem::class)
        );

        $excludedDirs = $excludeDirsProvider->get($input, $output, $dirsInCurrentDir, $sourceDirs);

        $phpUnitConfigPathProvider = new TestFrameworkConfigPathProvider($testFrameworkConfigLocator, $consoleHelper, $questionHelper);
        $phpUnitConfigPath = $phpUnitConfigPathProvider->get($input, $output, $dirsInCurrentDir, $input->getOption('test-framework'));

        $phpUnitExecutableFinder = new TestFrameworkFinder(TestFrameworkTypes::PHPUNIT);
        $phpUnitCustomExecutablePathProvider = new PhpUnitCustomExecutablePathProvider($phpUnitExecutableFinder, $consoleHelper, $questionHelper);
        $phpUnitCustomExecutablePath = $phpUnitCustomExecutablePathProvider->get($input, $output);

        $timeoutProvider = new TimeoutProvider($consoleHelper, $questionHelper);
        $timeout = $timeoutProvider->get($input, $output);

        $textLogFileProvider = new TextLogFileProvider($consoleHelper, $questionHelper);
        $textLogFilePath = $textLogFileProvider->get($input, $output, $dirsInCurrentDir);

        $this->saveConfig($sourceDirs, $excludedDirs, $timeout, $phpUnitConfigPath, $phpUnitCustomExecutablePath, $textLogFilePath);

        $output->writeln([
            '',
            sprintf(
                'Configuration file "<comment>%s</comment>" was created.',
                InfectionConfig::CONFIG_FILE_NAME . '.dist'
            ),
            '',
        ]);

        return 0;
    }

    private function saveConfig(
        array $sourceDirs,
        array $excludedDirs,
        int $timeout,
        string $phpUnitConfigPath = null,
        string $phpUnitCustomExecutablePath = null,
        string $textLogFilePath = null
    ): void {
        $configObject = new \stdClass();

        $configObject->timeout = $timeout;
        $configObject->source = new \stdClass();

        if ($sourceDirs) {
            $configObject->source->directories = $sourceDirs;
        }

        if ($excludedDirs) {
            $configObject->source->excludes = $excludedDirs;
        }

        if ($phpUnitConfigPath) {
            $configObject->phpUnit = new \stdClass();
            $configObject->phpUnit->configDir = $phpUnitConfigPath;
        }

        if ($phpUnitCustomExecutablePath) {
            if (!isset($configObject->phpUnit)) {
                $configObject->phpUnit = new \stdClass();
            }

            $configObject->phpUnit->customPath = $phpUnitCustomExecutablePath;
        }

        if ($textLogFilePath) {
            $configObject->logs = new \stdClass();
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

        file_put_contents(InfectionConfig::CONFIG_FILE_NAME . '.dist', json_encode($configObject, JSON_PRETTY_PRINT));
    }
}
