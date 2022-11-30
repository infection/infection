<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Console\Command;

use _HumbugBoxb47773b41c19\Fidry\Console\Command\Command;
use _HumbugBoxb47773b41c19\Fidry\Console\Command\Configuration;
use _HumbugBoxb47773b41c19\Fidry\Console\Command\Configuration as CommandConfiguration;
use _HumbugBoxb47773b41c19\Fidry\Console\Input\IO;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Helper\FormatterHelper;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputOption;
use _HumbugBoxb47773b41c19\Symfony\Component\Filesystem\Filesystem;
use function file_exists;
use function _HumbugBoxb47773b41c19\Safe\getcwd;
use function sprintf;
use const DIRECTORY_SEPARATOR;
final class InitCommand implements Command
{
    private const CONFIG_FILE_OPT = 'config';
    private const CONFIG_FILE_TEMPLATE = __DIR__ . '/../../scoper.inc.php.tpl';
    private const CONFIG_FILE_DEFAULT = 'scoper.inc.php';
    public function __construct(private readonly Filesystem $fileSystem, private readonly FormatterHelper $formatterHelper)
    {
    }
    public function getConfiguration() : CommandConfiguration
    {
        return new Configuration('init', 'Generates a configuration file.', '', [], [ChangeableDirectory::createOption(), new InputOption(self::CONFIG_FILE_OPT, 'c', InputOption::VALUE_REQUIRED, sprintf('Configuration file. Will use "%s" if found by default.', self::CONFIG_FILE_DEFAULT), null)]);
    }
    public function execute(IO $io) : int
    {
        ChangeableDirectory::changeWorkingDirectory($io);
        $io->newLine();
        $io->writeln($this->formatterHelper->formatSection('PHP-Scoper configuration generate', 'Welcome!'));
        $configFile = $this->retrieveConfig($io);
        if (null === $configFile) {
            $io->writeln('Skipping configuration file generator.');
            return 0;
        }
        $this->fileSystem->copy(self::CONFIG_FILE_TEMPLATE, $configFile);
        $io->writeln(['', sprintf('Generated the configuration file "<comment>%s</comment>".', $configFile), '']);
        return 0;
    }
    private function retrieveConfig(IO $io) : ?string
    {
        $configFile = $io->getOption(self::CONFIG_FILE_OPT)->asNullableNonEmptyString();
        $configFile = null === $configFile ? $this->makeAbsolutePath(self::CONFIG_FILE_DEFAULT) : $this->makeAbsolutePath($configFile);
        if (file_exists($configFile)) {
            $canDeleteFile = $io->confirm(sprintf('The configuration file "<comment>%s</comment>" already exists. Are you sure you want to ' . 'replace it?', $configFile), \false);
            if (!$canDeleteFile) {
                $io->writeln('Skipped file generation.');
                return $configFile;
            }
            $this->fileSystem->remove($configFile);
        } else {
            $createConfig = $io->confirm('No configuration file found. Do you want to create one?');
            if (!$createConfig) {
                return null;
            }
        }
        return $configFile;
    }
    private function makeAbsolutePath(string $path) : string
    {
        if (!$this->fileSystem->isAbsolutePath($path)) {
            $path = getcwd() . DIRECTORY_SEPARATOR . $path;
        }
        return $path;
    }
}
