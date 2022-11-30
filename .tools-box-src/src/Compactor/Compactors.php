<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Compactor;

use function array_reduce;
use function count;
use Countable;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\SymbolsRegistry;
use _HumbugBoxb47773b41c19\KevinGH\Box\PhpScoper\Scoper;
final class Compactors implements Compactor, Countable
{
    private array $compactors;
    private ?PhpScoper $scoperCompactor = null;
    public function __construct(Compactor ...$compactors)
    {
        $this->compactors = $compactors;
        foreach ($compactors as $compactor) {
            if ($compactor instanceof PhpScoper) {
                $this->scoperCompactor = $compactor;
                break;
            }
        }
    }
    public function compact(string $file, string $contents) : string
    {
        return array_reduce($this->compactors, static fn(string $contents, Compactor $compactor): string => $compactor->compact($file, $contents), $contents);
    }
    public function getScoper() : ?Scoper
    {
        return $this->scoperCompactor?->getScoper();
    }
    public function getScoperSymbolsRegistry() : ?SymbolsRegistry
    {
        return $this->scoperCompactor?->getScoper()->getSymbolsRegistry();
    }
    public function registerSymbolsRegistry(SymbolsRegistry $symbolsRegistry) : void
    {
        $this->scoperCompactor?->getScoper()->changeSymbolsRegistry($symbolsRegistry);
    }
    public function toArray() : array
    {
        return $this->compactors;
    }
    public function count() : int
    {
        return count($this->compactors);
    }
}
