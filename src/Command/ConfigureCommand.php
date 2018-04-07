<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
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

/**
 * @internal
 */
final class ConfigureCommand extends BaseCommand
{
    protected function configure()
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
            $output->writeln('Infection config generator requires an interactive mode.');

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
            $content = json_decode(file_get_contents('composer.json'));

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
            $this->getContainer()->get('filesystem')
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
    ) {
        $configObject = new \stdClass();

        $configObject->timeout = $timeout;
        $configObject->source = new \stdClass();

        if ($sourceDirs) {
            $configObject->source->directories = $sourceDirs;
        }

        if ($excludedDirs) {
            $configObject->source->exclude = $excludedDirs;
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

        file_put_contents(InfectionConfig::CONFIG_FILE_NAME . '.dist', json_encode($configObject, JSON_PRETTY_PRINT));
    }
}
