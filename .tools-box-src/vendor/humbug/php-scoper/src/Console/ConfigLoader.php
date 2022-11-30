<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Console;

use _HumbugBoxb47773b41c19\Fidry\Console\Command\CommandRegistry;
use _HumbugBoxb47773b41c19\Fidry\Console\Input\IO;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Configuration\Configuration;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Configuration\ConfigurationFactory;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\RuntimeException;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\StringInput;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\OutputInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Filesystem\Filesystem;
use _HumbugBoxb47773b41c19\Symfony\Component\Filesystem\Path;
use function assert;
use function count;
use function file_exists;
use function sprintf;
use function trim;
use const DIRECTORY_SEPARATOR;
final class ConfigLoader
{
    public function __construct(private readonly CommandRegistry $commandRegistry, private readonly Filesystem $fileSystem, private readonly ConfigurationFactory $configFactory)
    {
    }
    public function loadConfig(IO $io, string $prefix, bool $noConfig, ?string $configFilePath, string $defaultConfigFilePath, bool $isInitCommandExecuted, array $paths, string $cwd) : Configuration
    {
        $prefix = trim($prefix);
        $defaultConfigFilePath = $this->canonicalizePath($defaultConfigFilePath, $cwd);
        if ($noConfig) {
            return $this->loadConfigWithoutConfigFile($io, $prefix, $paths, $cwd);
        }
        if (null === $configFilePath && !$isInitCommandExecuted) {
            $configFilePath = $this->loadDefaultConfig($io, $defaultConfigFilePath);
            if (null === $configFilePath) {
                return $this->loadConfig($io, $prefix, $noConfig, $configFilePath, $defaultConfigFilePath, \true, $paths, $cwd);
            }
        }
        self::logConfigFilePathFound($io, $configFilePath);
        return $this->loadConfiguration($configFilePath, $prefix, $paths, $cwd);
    }
    private function loadConfigWithoutConfigFile(IO $io, string $prefix, array $paths, string $cwd) : Configuration
    {
        $io->writeln('Loading without configuration file.', OutputInterface::VERBOSITY_DEBUG);
        return $this->loadConfiguration(null, $prefix, $paths, $cwd);
    }
    private function loadDefaultConfig(IO $io, string $defaultConfigFilePath) : ?string
    {
        $configFilePath = $defaultConfigFilePath;
        if (file_exists($configFilePath)) {
            return $configFilePath;
        }
        $initInput = new StringInput('');
        $initInput->setInteractive($io->isInteractive());
        $this->commandRegistry->getCommand('init')->execute(new IO($initInput, $io->getOutput()));
        $io->writeln(sprintf('Config file "<comment>%s</comment>" not found. Skipping.', $configFilePath), OutputInterface::VERBOSITY_DEBUG);
        return null;
    }
    private static function logConfigFilePathFound(IO $io, ?string $configFilePath) : void
    {
        if (null === $configFilePath) {
            $io->writeln('Loading without configuration file.', OutputInterface::VERBOSITY_DEBUG);
            return;
        }
        if (!file_exists($configFilePath)) {
            throw new RuntimeException(sprintf('Could not find the configuration file "%s".', $configFilePath));
        }
        $io->writeln(sprintf('Using the configuration file "%s".', $configFilePath), OutputInterface::VERBOSITY_DEBUG);
    }
    private function loadConfiguration(?string $configFilePath, string $prefix, array $paths, string $cwd) : Configuration
    {
        return $this->configurePaths($this->configurePrefix($this->configFactory->create($configFilePath, $paths), $prefix), $cwd);
    }
    private function configurePrefix(Configuration $config, string $prefix) : Configuration
    {
        return '' !== $prefix ? $this->configFactory->createWithPrefix($config, $prefix) : $config;
    }
    private function configurePaths(Configuration $config, string $cwd) : Configuration
    {
        return 0 === count($config->getFilesWithContents()) ? $this->configFactory->createWithPaths($config, [$cwd]) : $config;
    }
    private function canonicalizePath(string $path, string $cwd) : string
    {
        $canonicalPath = Path::canonicalize($this->fileSystem->isAbsolutePath($path) ? $path : $cwd . DIRECTORY_SEPARATOR . $path);
        assert('' !== $canonicalPath);
        return $canonicalPath;
    }
}
