<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol;

use Countable;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name\FullyQualified;
use function array_values;
use function count;
final class SymbolsRegistry implements Countable
{
    private array $recordedFunctions = [];
    private array $recordedClasses = [];
    public static function createFromRegistries(array $symbolsRegistries) : self
    {
        $symbolsRegistry = new self();
        foreach ($symbolsRegistries as $symbolsRegistryToMerge) {
            $symbolsRegistry->merge($symbolsRegistryToMerge);
        }
        return $symbolsRegistry;
    }
    public function merge(self $symbolsRegistry) : void
    {
        foreach ($symbolsRegistry->getRecordedFunctions() as [$original, $alias]) {
            $this->recordedFunctions[$original] = [$original, $alias];
        }
        foreach ($symbolsRegistry->getRecordedClasses() as [$original, $alias]) {
            $this->recordedClasses[$original] = [$original, $alias];
        }
    }
    public function recordFunction(FullyQualified $original, FullyQualified $alias) : void
    {
        $this->recordedFunctions[(string) $original] = [(string) $original, (string) $alias];
    }
    public function getRecordedFunctions() : array
    {
        return array_values($this->recordedFunctions);
    }
    public function recordClass(FullyQualified $original, FullyQualified $alias) : void
    {
        $this->recordedClasses[(string) $original] = [(string) $original, (string) $alias];
    }
    public function getRecordedClass(string $original) : ?array
    {
        return $this->recordedClasses[$original] ?? null;
    }
    public function getRecordedClasses() : array
    {
        return array_values($this->recordedClasses);
    }
    public function count() : int
    {
        return count($this->recordedFunctions) + count($this->recordedClasses);
    }
}
