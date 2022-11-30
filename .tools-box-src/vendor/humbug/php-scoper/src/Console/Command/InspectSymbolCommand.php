<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Console\Command;

use _HumbugBoxb47773b41c19\Fidry\Console\Command\Command;
use _HumbugBoxb47773b41c19\Fidry\Console\Command\CommandRegistry;
use _HumbugBoxb47773b41c19\Fidry\Console\Command\Configuration as CommandConfiguration;
use _HumbugBoxb47773b41c19\Fidry\Console\ExitCode;
use _HumbugBoxb47773b41c19\Fidry\Console\Input\IO;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Configuration\Configuration;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Configuration\ConfigurationFactory;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Console\ConfigLoader;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\EnrichedReflector;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\EnrichedReflectorFactory;
use InvalidArgumentException;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Application as DummyApplication;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputArgument;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputOption;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\NullOutput;
use _HumbugBoxb47773b41c19\Symfony\Component\Filesystem\Filesystem;
use _HumbugBoxb47773b41c19\Symfony\Component\Filesystem\Path;
use function assert;
use function file_exists;
use function implode;
use function _HumbugBoxb47773b41c19\Safe\getcwd;
use function sprintf;
use const DIRECTORY_SEPARATOR;
final class InspectSymbolCommand implements Command
{
    private const SYMBOL_ARG = 'symbol';
    private const SYMBOL_TYPE_ARG = 'type';
    private const CONFIG_FILE_OPT = 'config';
    private const NO_CONFIG_OPT = 'no-config';
    public function __construct(private readonly Filesystem $fileSystem, private readonly ConfigurationFactory $configFactory, private readonly EnrichedReflectorFactory $enrichedReflectorFactory)
    {
    }
    public function getConfiguration() : CommandConfiguration
    {
        return new CommandConfiguration('inspect-symbol', 'Checks the given symbol for a given configuration. Helpful to have an insight on how PHP-Scoper will interpret this symbol', '', [new InputArgument(self::SYMBOL_ARG, InputArgument::REQUIRED, 'The symbol to inspect.'), new InputArgument(self::SYMBOL_TYPE_ARG, InputArgument::OPTIONAL, sprintf('The symbol type inspect ("%s").', implode('", "', SymbolType::values())), SymbolType::ANY_TYPE->value)], [ChangeableDirectory::createOption(), new InputOption(self::CONFIG_FILE_OPT, 'c', InputOption::VALUE_REQUIRED, sprintf('Configuration file. Will use "%s" if found by default.', ConfigurationFactory::DEFAULT_FILE_NAME)), new InputOption(self::NO_CONFIG_OPT, null, InputOption::VALUE_NONE, 'Do not look for a configuration file.')]);
    }
    public function execute(IO $io) : int
    {
        $io->newLine();
        ChangeableDirectory::changeWorkingDirectory($io);
        $cwd = getcwd();
        $symbol = $io->getArgument(self::SYMBOL_ARG)->asString();
        $symbolType = self::getSymbolType($io);
        $config = $this->retrieveConfig($io, $cwd);
        $enrichedReflector = $this->enrichedReflectorFactory->create($config->getSymbolsConfiguration());
        self::printSymbol($io, $symbol, $symbolType, $config->getPath(), $enrichedReflector);
        return ExitCode::SUCCESS;
    }
    private static function getSymbolType(IO $io) : SymbolType
    {
        $symbolType = $io->getArgument(self::SYMBOL_TYPE_ARG)->asString();
        return SymbolType::from($symbolType);
    }
    private function retrieveConfig(IO $io, string $cwd) : Configuration
    {
        $configLoader = new ConfigLoader(new CommandRegistry(new DummyApplication()), $this->fileSystem, $this->configFactory);
        $configFilePath = $this->getConfigFilePath($io, $cwd);
        $noConfig = $io->getOption(self::NO_CONFIG_OPT)->asBoolean();
        if (null === $configFilePath) {
            $noConfig = \true;
        }
        return $configLoader->loadConfig(new IO($io->getInput(), new NullOutput()), '', $noConfig, $configFilePath, ConfigurationFactory::DEFAULT_FILE_NAME, \true, [__FILE__], getcwd());
    }
    private function getConfigFilePath(IO $io, string $cwd) : ?string
    {
        $configPath = (string) $io->getOption(self::CONFIG_FILE_OPT)->asNullableString();
        if ('' === $configPath) {
            $configPath = ConfigurationFactory::DEFAULT_FILE_NAME;
        }
        $configPath = $this->canonicalizePath($configPath, $cwd);
        return file_exists($configPath) ? $configPath : null;
    }
    private static function printSymbol(IO $io, string $symbol, SymbolType $type, ?string $configPath, EnrichedReflector $reflector) : void
    {
        self::printDocBlock($io);
        self::printConfigLoaded($io, $configPath);
        self::printInspectionHeadline($io, $symbol, $type);
        $io->newLine();
        if (!(SymbolType::ANY_TYPE === $type)) {
            self::printTypedSymbol($io, $symbol, $type, $reflector);
        } else {
            self::printAnyTypeSymbol($io, $symbol, $reflector);
        }
    }
    private static function printDocBlock(IO $io) : void
    {
        $io->writeln(['Internal (configured via the `excluded-*` settings) are treated as PHP native symbols, i.e. will remain untouched.', 'Exposed symbols (configured via the `expose-*` settings) will be prefixed but aliased to its original symbol.', 'If a symbol is neither internal or exposed, it will be prefixed and not aliased', '', 'For more information, see:']);
        $io->listing(['<href=https://github.com/humbug/php-scoper/blob/master/docs/configuration.md#excluded-symbols>Doc link for excluded symbols</>', '<href=https://github.com/humbug/php-scoper/blob/master/docs/configuration.md#exposed-symbols>Doc link for exposed symbols</>']);
    }
    private static function printConfigLoaded(IO $io, ?string $configPath) : void
    {
        $io->writeln(null === $configPath ? 'No configuration loaded.' : sprintf('Loaded the configuration <comment>%s</comment>', $configPath));
        $io->newLine();
    }
    private static function printInspectionHeadline(IO $io, string $symbol, SymbolType $type) : void
    {
        $io->writeln(sprintf('Inspecting the symbol <comment>%s</comment> %s', $symbol, SymbolType::ANY_TYPE === $type ? 'for all types.' : sprintf('for type <comment>%s</comment>:', $type->value)));
    }
    private static function printAnyTypeSymbol(IO $io, string $symbol, EnrichedReflector $reflector) : void
    {
        foreach (SymbolType::getAllSpecificTypes() as $specificType) {
            $io->writeln(sprintf('As a <comment>%s</comment>:', $specificType->value));
            self::printTypedSymbol($io, $symbol, $specificType, $reflector);
        }
    }
    private static function printTypedSymbol(IO $io, string $symbol, SymbolType $type, EnrichedReflector $reflector) : void
    {
        [$internal, $exposed] = self::determineSymbolStatus($symbol, $type, $reflector);
        $io->listing([sprintf('Internal: %s', self::convertBoolToString($internal)), sprintf('Exposed:  %s', self::convertBoolToString($exposed))]);
    }
    private static function determineSymbolStatus(string $symbol, SymbolType $type, EnrichedReflector $reflector) : array
    {
        return match ($type) {
            SymbolType::CLASS_TYPE => [$reflector->isClassInternal($symbol), $reflector->isExposedClass($symbol)],
            SymbolType::FUNCTION_TYPE => [$reflector->isFunctionInternal($symbol), $reflector->isExposedFunction($symbol)],
            SymbolType::CONSTANT_TYPE => [$reflector->isConstantInternal($symbol), $reflector->isExposedConstant($symbol)],
            default => throw new InvalidArgumentException(sprintf('Invalid type "%s"', $type->value)),
        };
    }
    private static function convertBoolToString(bool $bool) : string
    {
        return \true === $bool ? '<question>true</question>' : '<error>false</error>';
    }
    private function canonicalizePath(string $path, string $cwd) : string
    {
        $canonicalPath = Path::canonicalize($this->fileSystem->isAbsolutePath($path) ? $path : $cwd . DIRECTORY_SEPARATOR . $path);
        assert('' !== $canonicalPath);
        return $canonicalPath;
    }
}
