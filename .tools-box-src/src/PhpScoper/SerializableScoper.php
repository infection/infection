<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\PhpScoper;

use function count;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Configuration\Configuration as PhpScoperConfiguration;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Container as PhpScoperContainer;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Scoper\Scoper as PhpScoperScoper;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\SymbolsRegistry;
use function method_exists;
final class SerializableScoper implements Scoper
{
    private PhpScoperConfiguration $scoperConfig;
    private PhpScoperContainer $scoperContainer;
    private PhpScoperScoper $scoper;
    private SymbolsRegistry $symbolsRegistry;
    private array $excludedFilePaths;
    public function __construct(PhpScoperConfiguration $scoperConfig, string ...$excludedFilePaths)
    {
        if (method_exists($scoperConfig, 'withPatcher')) {
            $this->scoperConfig = $scoperConfig->withPatcher(PatcherFactory::createSerializablePatchers($scoperConfig->getPatcher()));
        } else {
            $this->scoperConfig = new PhpScoperConfiguration($scoperConfig->getPath(), $scoperConfig->getPrefix(), $scoperConfig->getFilesWithContents(), $scoperConfig->getExcludedFilesWithContents(), PatcherFactory::createSerializablePatchers($scoperConfig->getPatcher()), $scoperConfig->getSymbolsConfiguration());
        }
        $this->excludedFilePaths = $excludedFilePaths;
        $this->symbolsRegistry = new SymbolsRegistry();
    }
    public function scope(string $filePath, string $contents) : string
    {
        return $this->getScoper()->scope($filePath, $contents);
    }
    public function changeSymbolsRegistry(SymbolsRegistry $symbolsRegistry) : void
    {
        $this->symbolsRegistry = $symbolsRegistry;
        unset($this->scoper);
    }
    public function getSymbolsRegistry() : SymbolsRegistry
    {
        return $this->symbolsRegistry;
    }
    public function getPrefix() : string
    {
        return $this->scoperConfig->getPrefix();
    }
    private function getScoper() : PhpScoperScoper
    {
        if (isset($this->scoper)) {
            return $this->scoper;
        }
        if (!isset($this->scoperContainer)) {
            $this->scoperContainer = new PhpScoperContainer();
        }
        $this->scoper = $this->createScoper();
        return $this->scoper;
    }
    public function __wakeup() : void
    {
        unset($this->scoper, $this->scoperContainer);
    }
    private function createScoper() : PhpScoperScoper
    {
        $scoper = $this->scoperContainer->getScoperFactory()->createScoper($this->scoperConfig, $this->symbolsRegistry);
        if (0 === count($this->excludedFilePaths)) {
            return $scoper;
        }
        return new ExcludedFilesScoper($scoper, ...$this->excludedFilePaths);
    }
}
