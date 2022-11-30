<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Scoper;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Scoper\Symfony\XmlScoper as SymfonyXmlScoper;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Scoper\Symfony\YamlScoper as SymfonyYamlScoper;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\EnrichedReflector;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\SymbolsRegistry;
use _HumbugBoxb47773b41c19\PhpParser\Error as PhpParserError;
use function func_get_args;
final class SymfonyScoper implements Scoper
{
    private readonly SymfonyXmlScoper $decoratedScoper;
    public function __construct(Scoper $decoratedScoper, string $prefix, EnrichedReflector $enrichedReflector, SymbolsRegistry $symbolsRegistry)
    {
        $this->decoratedScoper = new SymfonyXmlScoper(new SymfonyYamlScoper($decoratedScoper, $prefix, $enrichedReflector, $symbolsRegistry), $prefix, $enrichedReflector, $symbolsRegistry);
    }
    public function scope(string $filePath, string $contents) : string
    {
        return $this->decoratedScoper->scope(...func_get_args());
    }
}
