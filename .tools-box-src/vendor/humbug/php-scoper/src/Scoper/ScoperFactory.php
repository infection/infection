<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Scoper;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Configuration\Configuration;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\Printer\Printer;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\TraverserFactory;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Scoper\Composer\AutoloadPrefixer;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Scoper\Composer\InstalledPackagesScoper;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Scoper\Composer\JsonFileScoper;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\EnrichedReflectorFactory;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\SymbolsRegistry;
use _HumbugBoxb47773b41c19\PhpParser\Lexer;
use _HumbugBoxb47773b41c19\PhpParser\Parser;
class ScoperFactory
{
    public function __construct(private readonly Parser $parser, private readonly EnrichedReflectorFactory $enrichedReflectorFactory, private readonly Printer $printer, private readonly Lexer $lexer)
    {
    }
    public function createScoper(Configuration $configuration, SymbolsRegistry $symbolsRegistry) : Scoper
    {
        $prefix = $configuration->getPrefix();
        $symbolsConfiguration = $configuration->getSymbolsConfiguration();
        $enrichedReflector = $this->enrichedReflectorFactory->create($symbolsConfiguration);
        $autoloadPrefixer = new AutoloadPrefixer($prefix, $enrichedReflector);
        return new PatchScoper(new PhpScoper($this->parser, new JsonFileScoper(new InstalledPackagesScoper(new SymfonyScoper(new NullScoper(), $prefix, $enrichedReflector, $symbolsRegistry), $autoloadPrefixer), $autoloadPrefixer), new TraverserFactory($enrichedReflector, $prefix, $symbolsRegistry), $this->printer, $this->lexer), $prefix, $configuration->getPatcher());
    }
}
