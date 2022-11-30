<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Configuration\ConfigurationFactory;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Configuration\RegexChecker;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Configuration\SymbolsConfigurationFactory;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\Printer\Printer;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\Printer\StandardPrinter;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Scoper\ScoperFactory;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\EnrichedReflectorFactory;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\Reflector;
use _HumbugBoxb47773b41c19\PhpParser\Lexer;
use _HumbugBoxb47773b41c19\PhpParser\Parser;
use _HumbugBoxb47773b41c19\PhpParser\ParserFactory;
use _HumbugBoxb47773b41c19\PhpParser\PrettyPrinter\Standard;
use _HumbugBoxb47773b41c19\Symfony\Component\Filesystem\Filesystem;
final class Container
{
    private Filesystem $filesystem;
    private ConfigurationFactory $configFactory;
    private Parser $parser;
    private Reflector $reflector;
    private ScoperFactory $scoperFactory;
    private EnrichedReflectorFactory $enrichedReflectorFactory;
    private Printer $printer;
    private Lexer $lexer;
    public function getFileSystem() : Filesystem
    {
        if (!isset($this->filesystem)) {
            $this->filesystem = new Filesystem();
        }
        return $this->filesystem;
    }
    public function getConfigurationFactory() : ConfigurationFactory
    {
        if (!isset($this->configFactory)) {
            $this->configFactory = new ConfigurationFactory($this->getFileSystem(), new SymbolsConfigurationFactory(new RegexChecker()));
        }
        return $this->configFactory;
    }
    public function getScoperFactory() : ScoperFactory
    {
        if (!isset($this->scoperFactory)) {
            $this->scoperFactory = new ScoperFactory($this->getParser(), $this->getEnrichedReflectorFactory(), $this->getPrinter(), $this->getLexer());
        }
        return $this->scoperFactory;
    }
    public function getParser() : Parser
    {
        if (!isset($this->parser)) {
            $this->parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $this->getLexer());
        }
        return $this->parser;
    }
    public function getReflector() : Reflector
    {
        if (!isset($this->reflector)) {
            $this->reflector = Reflector::createWithPhpStormStubs();
        }
        return $this->reflector;
    }
    public function getEnrichedReflectorFactory() : EnrichedReflectorFactory
    {
        if (!isset($this->enrichedReflectorFactory)) {
            $this->enrichedReflectorFactory = new EnrichedReflectorFactory($this->getReflector());
        }
        return $this->enrichedReflectorFactory;
    }
    public function getPrinter() : Printer
    {
        if (!isset($this->printer)) {
            $this->printer = new StandardPrinter(new Standard());
        }
        return $this->printer;
    }
    public function getLexer() : Lexer
    {
        if (!isset($this->lexer)) {
            $this->lexer = new Lexer();
        }
        return $this->lexer;
    }
}
