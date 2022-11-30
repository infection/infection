<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Command;

use _HumbugBox9658796bb9f0\Composer\InstalledVersions;
use function count;
use function file_exists;
use const GLOB_ONLYDIR;
use function implode;
use _HumbugBox9658796bb9f0\Infection\Config\ConsoleHelper;
use _HumbugBox9658796bb9f0\Infection\Config\Guesser\SourceDirGuesser;
use _HumbugBox9658796bb9f0\Infection\Config\ValueProvider\ExcludeDirsProvider;
use _HumbugBox9658796bb9f0\Infection\Config\ValueProvider\PhpUnitCustomExecutablePathProvider;
use _HumbugBox9658796bb9f0\Infection\Config\ValueProvider\SourceDirsProvider;
use _HumbugBox9658796bb9f0\Infection\Config\ValueProvider\TestFrameworkConfigPathProvider;
use _HumbugBox9658796bb9f0\Infection\Config\ValueProvider\TextLogFileProvider;
use _HumbugBox9658796bb9f0\Infection\Configuration\Schema\SchemaConfigurationLoader;
use _HumbugBox9658796bb9f0\Infection\Console\Application;
use _HumbugBox9658796bb9f0\Infection\Console\IO;
use _HumbugBox9658796bb9f0\Infection\FileSystem\Finder\TestFrameworkFinder;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Config\TestFrameworkConfigLocator;
use _HumbugBox9658796bb9f0\Infection\TestFramework\TestFrameworkTypes;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use OutOfBoundsException;
use RuntimeException;
use function _HumbugBox9658796bb9f0\Safe\file_get_contents;
use function _HumbugBox9658796bb9f0\Safe\file_put_contents;
use function _HumbugBox9658796bb9f0\Safe\glob;
use function _HumbugBox9658796bb9f0\Safe\json_decode;
use function _HumbugBox9658796bb9f0\Safe\json_encode;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use stdClass;
use function strpos;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\InputOption;
final class ConfigureCommand extends BaseCommand
{
    public const NONINTERACTIVE_MODE_ERROR = 'Infection config generator requires an interactive mode.';
    private const OPTION_TEST_FRAMEWORK = 'test-framework';
    protected function configure() : void
    {
        $this->setName('configure')->setDescription('Create Infection config')->addOption(self::OPTION_TEST_FRAMEWORK, null, InputOption::VALUE_REQUIRED, sprintf('Name of the Test framework to use ("%s")', implode('", "', TestFrameworkTypes::TYPES)), TestFrameworkTypes::PHPUNIT);
    }
    protected function executeCommand(IO $io) : bool
    {
        if (!$io->isInteractive()) {
            $io->writeln(self::NONINTERACTIVE_MODE_ERROR);
            $this->abort();
        }
        $consoleHelper = new ConsoleHelper($this->getHelper('formatter'));
        $consoleHelper->writeSection($io->getOutput(), 'Welcome to the Infection config generator');
        $io->newLine();
        $io->writeln('We did not find a configuration file. The following questions will help us to generate it for you.');
        $io->newLine();
        $dirsInCurrentDir = glob('*', GLOB_ONLYDIR);
        $testFrameworkConfigLocator = new TestFrameworkConfigLocator('.');
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
        $excludeDirsProvider = new ExcludeDirsProvider($consoleHelper, $questionHelper, $fileSystem);
        $excludedDirs = $excludeDirsProvider->get($io, $dirsInCurrentDir, $sourceDirs);
        $phpUnitConfigPathProvider = new TestFrameworkConfigPathProvider($testFrameworkConfigLocator, $consoleHelper, $questionHelper);
        $phpUnitConfigPath = $phpUnitConfigPathProvider->get($io, $dirsInCurrentDir, $io->getInput()->getOption(self::OPTION_TEST_FRAMEWORK));
        $phpUnitExecutableFinder = new TestFrameworkFinder();
        $phpUnitCustomExecutablePathProvider = new PhpUnitCustomExecutablePathProvider($phpUnitExecutableFinder, $consoleHelper, $questionHelper);
        $phpUnitCustomExecutablePath = $phpUnitCustomExecutablePathProvider->get($io);
        $textLogFileProvider = new TextLogFileProvider($consoleHelper, $questionHelper);
        $textLogFilePath = $textLogFileProvider->get($io, $dirsInCurrentDir);
        $this->saveConfig($sourceDirs, $excludedDirs, $phpUnitConfigPath, $phpUnitCustomExecutablePath, $textLogFilePath);
        $io->newLine();
        $io->writeln(sprintf('Configuration file "<comment>%s</comment>" was created.', SchemaConfigurationLoader::DEFAULT_JSON5_CONFIG_FILE));
        $io->newLine();
        return \true;
    }
    private function saveConfig(array $sourceDirs, array $excludedDirs, ?string $phpUnitConfigPath = null, ?string $phpUnitCustomExecutablePath = null, ?string $textLogFilePath = null) : void
    {
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
        $configObject->mutators = ['@default' => \true];
        file_put_contents(SchemaConfigurationLoader::DEFAULT_JSON5_CONFIG_FILE, json_encode($configObject, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
    private function abort() : void
    {
        throw new RuntimeException('Configuration generation aborted');
    }
    private function getJsonSchemaPathOrUrl() : string
    {
        $fileName = 'vendor/infection/infection/resources/schema.json';
        if (file_exists($fileName)) {
            return $fileName;
        }
        try {
            $version = InstalledVersions::getPrettyVersion(Application::PACKAGE_NAME);
            if ($version === null || strpos($version, 'dev-') === 0) {
                $version = 'master';
            }
        } catch (OutOfBoundsException $e) {
            $version = 'master';
        }
        return sprintf('https://raw.githubusercontent.com/infection/infection/%s/resources/schema.json', $version);
    }
}
