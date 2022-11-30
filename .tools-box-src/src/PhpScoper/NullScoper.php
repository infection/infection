<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\PhpScoper;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\SymbolsRegistry;
final class NullScoper implements Scoper
{
    public function __construct(private SymbolsRegistry $symbolsRegistry = new SymbolsRegistry())
    {
    }
    public function scope(string $filePath, string $contents) : string
    {
        return $contents;
    }
    public function changeSymbolsRegistry(SymbolsRegistry $symbolsRegistry) : void
    {
        $this->symbolsRegistry = $symbolsRegistry;
    }
    public function getSymbolsRegistry() : SymbolsRegistry
    {
        return $this->symbolsRegistry;
    }
    public function getPrefix() : string
    {
        return '';
    }
}
