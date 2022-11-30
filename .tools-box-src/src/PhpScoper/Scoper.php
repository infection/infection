<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\PhpScoper;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\SymbolsRegistry;
interface Scoper
{
    public function scope(string $filePath, string $contents) : string;
    public function changeSymbolsRegistry(SymbolsRegistry $symbolsRegistry) : void;
    public function getSymbolsRegistry() : SymbolsRegistry;
    public function getPrefix() : string;
}
