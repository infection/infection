<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Console\Command;

use _HumbugBoxb47773b41c19\Fidry\Console\Application\Application;
use _HumbugBoxb47773b41c19\Fidry\Console\Command\Command;
use _HumbugBoxb47773b41c19\Fidry\Console\Command\CommandAware;
use _HumbugBoxb47773b41c19\Fidry\Console\Command\CommandAwareness;
use _HumbugBoxb47773b41c19\Fidry\Console\Command\Configuration as CommandConfiguration;
use _HumbugBoxb47773b41c19\Fidry\Console\ExitCode;
use _HumbugBoxb47773b41c19\Fidry\Console\Input\IO;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Configuration\Configuration;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Configuration\ConfigurationFactory;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Console\ConfigLoader;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Console\ConsoleScoper;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Scoper\ScoperFactory;
use InvalidArgumentException;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\RuntimeException;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputArgument;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputOption;
use _HumbugBoxb47773b41c19\Symfony\Component\Filesystem\Filesystem;
use _HumbugBoxb47773b41c19\Symfony\Component\Filesystem\Path;
use function array_map;
use function is_dir;
use function is_writable;
use function _HumbugBoxb47773b41c19\Safe\getcwd;
use function sprintf;
use const DIRECTORY_SEPARATOR;
final class AddPrefixCommand implements Command, CommandAware
{
    use CommandAwareness;
    private const PATH_ARG = 'paths';
    private const PREFIX_OPT = 'prefix';
    private const OUTPUT_DIR_OPT = 'output-dir';
    private const FORCE_OPT = 'force';
    private const STOP_ON_FAILURE_OPT = 'stop-on-failure';
    private const CONFIG_FILE_OPT = 'config';
    private const NO_CONFIG_OPT = 'no-config';
    private const DEFAULT_OUTPUT_DIR = 'build';
    private bool $init = \false;
    public function __construct(private readonly Filesystem $fileSystem, private readonly ScoperFactory $scoperFactory, private readonly Application $application, private readonly ConfigurationFactory $configFactory)
    {
    }
    public function getConfiguration() : CommandConfiguration
    {
        return new CommandConfiguration('add-prefix', 'Goes through all the PHP files found in the given paths to apply the given prefix to namespaces & FQNs.', '', [new InputArgument(self::PATH_ARG, InputArgument::IS_ARRAY, 'The path(s) to process.')], [ChangeableDirectory::createOption(), new InputOption(self::PREFIX_OPT, 'p', InputOption::VALUE_REQUIRED, 'The namespace prefix to add.', ''), new InputOption(self::OUTPUT_DIR_OPT, 'o', InputOption::VALUE_REQUIRED, 'The output directory in which the prefixed code will be dumped.'), new InputOption(self::FORCE_OPT, 'f', InputOption::VALUE_NONE, 'Deletes any existing content in the output directory without any warning.'), new InputOption(self::STOP_ON_FAILURE_OPT, 's', InputOption::VALUE_NONE, 'Stops on failure.'), new InputOption(self::CONFIG_FILE_OPT, 'c', InputOption::VALUE_REQUIRED, sprintf('Configuration file. Will use "%s" if found by default.', ConfigurationFactory::DEFAULT_FILE_NAME)), new InputOption(self::NO_CONFIG_OPT, null, InputOption::VALUE_NONE, 'Do not look for a configuration file.')]);
    }
    public function execute(IO $io) : int
    {
        $io->newLine();
        ChangeableDirectory::changeWorkingDirectory($io);
        $cwd = getcwd();
        $paths = $this->getPathArguments($io, $cwd);
        $config = $this->retrieveConfig($io, $paths, $cwd);
        $outputDir = $this->canonicalizePath($this->getOutputDir($io, $config), $cwd);
        $this->checkOutputDir($io, $outputDir);
        $this->getScoper()->scope($io, $config, $paths, $outputDir, $io->getOption(self::STOP_ON_FAILURE_OPT)->asBoolean());
        return ExitCode::SUCCESS;
    }
    private function getOutputDir(IO $io, Configuration $configuration) : string
    {
        $commandOutputDir = $io->getOption(self::OUTPUT_DIR_OPT)->asString();
        if ('' !== $commandOutputDir) {
            return $commandOutputDir;
        }
        return $configuration->getOutputDir() ?? self::DEFAULT_OUTPUT_DIR;
    }
    private function checkOutputDir(IO $io, string $outputDir) : void
    {
        if (!$this->fileSystem->exists($outputDir)) {
            return;
        }
        self::checkPathIsWriteable($outputDir);
        $canDeleteFile = self::canDeleteOutputDir($io, $outputDir);
        if (!$canDeleteFile) {
            throw new RuntimeException('Cannot delete the output directory. Interrupting the process.');
        }
        $this->fileSystem->remove($outputDir);
    }
    private static function checkPathIsWriteable(string $path) : void
    {
        if (!is_writable($path)) {
            throw new RuntimeException(sprintf('Expected "<comment>%s</comment>" to be writeable.', $path));
        }
    }
    private static function canDeleteOutputDir(IO $io, string $outputDir) : bool
    {
        if ($io->getOption(self::FORCE_OPT)->asBoolean()) {
            return \true;
        }
        $question = sprintf(is_dir($outputDir) ? 'The output directory "<comment>%s</comment>" already exists. Continuing will erase its content, do you wish to proceed?' : 'Expected "<comment>%s</comment>" to be a directory but found a file instead. It will be  removed, do you wish to proceed?', $outputDir);
        return $io->confirm($question, \false);
    }
    private function retrieveConfig(IO $io, array $paths, string $cwd) : Configuration
    {
        $configLoader = new ConfigLoader($this->getCommandRegistry(), $this->fileSystem, $this->configFactory);
        return $configLoader->loadConfig($io, $io->getOption(self::PREFIX_OPT)->asString(), $io->getOption(self::NO_CONFIG_OPT)->asBoolean(), $this->getConfigFilePath($io, $cwd), ConfigurationFactory::DEFAULT_FILE_NAME, $this->init, $paths, $cwd);
    }
    private function getConfigFilePath(IO $io, string $cwd) : ?string
    {
        $configFilePath = (string) $io->getOption(self::CONFIG_FILE_OPT)->asNullableString();
        return '' === $configFilePath ? null : $this->canonicalizePath($configFilePath, $cwd);
    }
    private function getPathArguments(IO $io, string $cwd) : array
    {
        return array_map(fn(string $path) => $this->canonicalizePath($path, $cwd), $io->getArgument(self::PATH_ARG)->asNonEmptyStringList());
    }
    private function canonicalizePath(string $path, string $cwd) : string
    {
        $canonicalPath = Path::canonicalize($this->fileSystem->isAbsolutePath($path) ? $path : $cwd . DIRECTORY_SEPARATOR . $path);
        if ('' === $canonicalPath) {
            throw new InvalidArgumentException('Cannot canonicalize empty path and empty working directory');
        }
        return $canonicalPath;
    }
    private function getScoper() : ConsoleScoper
    {
        return new ConsoleScoper($this->fileSystem, $this->application, $this->scoperFactory);
    }
}
