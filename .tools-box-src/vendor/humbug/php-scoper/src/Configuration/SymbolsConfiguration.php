<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Configuration;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\NotInstantiable;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\NamespaceRegistry;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\SymbolRegistry;
final class SymbolsConfiguration
{
    use NotInstantiable;
    public static function create(bool $exposeGlobalConstants = \true, bool $exposeGlobalClasses = \true, bool $exposeGlobalFunctions = \true, ?NamespaceRegistry $excludedNamespaces = null, ?NamespaceRegistry $exposedNamespaces = null, ?SymbolRegistry $exposedClasses = null, ?SymbolRegistry $exposedFunctions = null, ?SymbolRegistry $exposedConstants = null, ?SymbolRegistry $excludedClasses = null, ?SymbolRegistry $excludedFunctions = null, ?SymbolRegistry $excludedConstants = null) : self
    {
        return new self($exposeGlobalConstants, $exposeGlobalClasses, $exposeGlobalFunctions, $excludedNamespaces ?? NamespaceRegistry::create(), $exposedNamespaces ?? NamespaceRegistry::create(), $exposedClasses ?? SymbolRegistry::create(), $exposedFunctions ?? SymbolRegistry::create(), $exposedConstants ?? SymbolRegistry::createForConstants(), $excludedClasses ?? SymbolRegistry::create(), $excludedFunctions ?? SymbolRegistry::create(), $excludedConstants ?? SymbolRegistry::createForConstants());
    }
    private function __construct(private bool $exposeGlobalConstants, private bool $exposeGlobalClasses, private bool $exposeGlobalFunctions, private NamespaceRegistry $excludedNamespaces, private NamespaceRegistry $exposedNamespaces, private SymbolRegistry $exposedClasses, private SymbolRegistry $exposedFunctions, private SymbolRegistry $exposedConstants, private SymbolRegistry $excludedClasses, private SymbolRegistry $excludedFunctions, private SymbolRegistry $excludedConstants)
    {
    }
    public function shouldExposeGlobalConstants() : bool
    {
        return $this->exposeGlobalConstants;
    }
    public function shouldExposeGlobalClasses() : bool
    {
        return $this->exposeGlobalClasses;
    }
    public function shouldExposeGlobalFunctions() : bool
    {
        return $this->exposeGlobalFunctions;
    }
    public function getExcludedNamespaces() : NamespaceRegistry
    {
        return $this->excludedNamespaces;
    }
    public function getExposedNamespaces() : NamespaceRegistry
    {
        return $this->exposedNamespaces;
    }
    public function getExposedClasses() : SymbolRegistry
    {
        return $this->exposedClasses;
    }
    public function getExposedFunctions() : SymbolRegistry
    {
        return $this->exposedFunctions;
    }
    public function getExposedConstants() : SymbolRegistry
    {
        return $this->exposedConstants;
    }
    public function getExcludedClasses() : SymbolRegistry
    {
        return $this->excludedClasses;
    }
    public function getExcludedFunctions() : SymbolRegistry
    {
        return $this->excludedFunctions;
    }
    public function getExcludedConstants() : SymbolRegistry
    {
        return $this->excludedConstants;
    }
}
