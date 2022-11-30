<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Configuration\SymbolsConfiguration;
use function ltrim;
use function str_contains;
final class EnrichedReflector
{
    public function __construct(private Reflector $reflector, private SymbolsConfiguration $symbolsConfiguration)
    {
    }
    public function belongsToExcludedNamespace(string $name) : bool
    {
        return $this->symbolsConfiguration->getExcludedNamespaces()->belongsToRegisteredNamespace($name);
    }
    private function belongsToExposedNamespace(string $name) : bool
    {
        return $this->symbolsConfiguration->getExposedNamespaces()->belongsToRegisteredNamespace($name);
    }
    public function isFunctionInternal(string $name) : bool
    {
        return $this->reflector->isFunctionInternal($name);
    }
    public function isFunctionExcluded(string $name) : bool
    {
        return $this->reflector->isFunctionInternal($name) || $this->belongsToExcludedNamespace($name);
    }
    public function isClassInternal(string $name) : bool
    {
        return $this->reflector->isClassInternal($name);
    }
    public function isClassExcluded(string $name) : bool
    {
        return $this->reflector->isClassInternal($name) || $this->belongsToExcludedNamespace($name);
    }
    public function isConstantInternal(string $name) : bool
    {
        return $this->reflector->isConstantInternal($name);
    }
    public function isExposedFunction(string $resolvedName) : bool
    {
        return !$this->isFunctionExcluded($resolvedName) && ($this->isExposedFunctionFromGlobalNamespaceWithoutExclusionCheck($resolvedName) || $this->symbolsConfiguration->getExposedFunctions()->matches($resolvedName) || $this->belongsToExposedNamespace($resolvedName));
    }
    public function isExposedFunctionFromGlobalNamespace(string $resolvedName) : bool
    {
        return !$this->isFunctionExcluded($resolvedName) && $this->isExposedFunctionFromGlobalNamespaceWithoutExclusionCheck($resolvedName);
    }
    public function isExposedClass(string $resolvedName) : bool
    {
        return !$this->isClassExcluded($resolvedName) && ($this->isExposedClassFromGlobalNamespaceWithoutExclusionCheck($resolvedName) || $this->symbolsConfiguration->getExposedClasses()->matches($resolvedName) || $this->belongsToExposedNamespace($resolvedName));
    }
    public function isExposedClassFromGlobalNamespace(string $resolvedName) : bool
    {
        return !$this->isClassExcluded($resolvedName) && $this->isExposedClassFromGlobalNamespaceWithoutExclusionCheck($resolvedName);
    }
    public function isExposedConstant(string $name) : bool
    {
        return !$this->belongsToExcludedNamespace($name) && ($this->reflector->isConstantInternal($name) || $this->isExposedConstantFromGlobalNamespace($name) || $this->symbolsConfiguration->getExposedConstants()->matches($name) || $this->belongsToExposedNamespace($name));
    }
    public function isExposedConstantFromGlobalNamespace(string $constantName) : bool
    {
        return $this->symbolsConfiguration->shouldExposeGlobalConstants() && $this->belongsToGlobalNamespace($constantName);
    }
    public function isExcludedNamespace(string $name) : bool
    {
        return $this->symbolsConfiguration->getExcludedNamespaces()->isRegisteredNamespace($name);
    }
    private function isExposedFunctionFromGlobalNamespaceWithoutExclusionCheck(string $functionName) : bool
    {
        return $this->symbolsConfiguration->shouldExposeGlobalFunctions() && $this->belongsToGlobalNamespace($functionName);
    }
    private function isExposedClassFromGlobalNamespaceWithoutExclusionCheck(string $className) : bool
    {
        return $this->symbolsConfiguration->shouldExposeGlobalClasses() && $this->belongsToGlobalNamespace($className);
    }
    public function belongsToGlobalNamespace(string $symbolName) : bool
    {
        return !str_contains(ltrim($symbolName, '\\'), '\\');
    }
}
