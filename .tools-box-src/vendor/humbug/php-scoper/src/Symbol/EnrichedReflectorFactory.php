<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Configuration\SymbolsConfiguration;
final class EnrichedReflectorFactory
{
    public function __construct(private Reflector $reflector)
    {
    }
    public function create(SymbolsConfiguration $symbolsConfiguration) : EnrichedReflector
    {
        $configuredReflector = $this->reflector->withAdditionalSymbols($symbolsConfiguration->getExcludedClasses(), $symbolsConfiguration->getExcludedFunctions(), $symbolsConfiguration->getExcludedConstants());
        return new EnrichedReflector($configuredReflector, $symbolsConfiguration);
    }
}
