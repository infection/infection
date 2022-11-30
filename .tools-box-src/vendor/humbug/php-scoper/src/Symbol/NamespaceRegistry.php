<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol;

use function array_filter;
use function array_map;
use function array_pop;
use function array_unique;
use function count;
use function explode;
use function implode;
use function ltrim;
use function _HumbugBoxb47773b41c19\Safe\preg_match;
use function str_contains;
use function strtolower;
use function trim;
use const SORT_STRING;
final class NamespaceRegistry
{
    private bool $containsGlobalNamespace;
    public static function create(array $namespaceNames = [], array $namespaceRegexes = []) : self
    {
        return new self(array_unique(array_map(static fn(string $namespaceName) => strtolower(trim($namespaceName, '\\')), $namespaceNames), SORT_STRING), array_unique($namespaceRegexes, SORT_STRING));
    }
    private function __construct(private array $names, private array $regexes)
    {
        $this->containsGlobalNamespace = count(array_filter($names, static fn(string $name) => '' === $name)) !== 0;
    }
    public function belongsToRegisteredNamespace(string $symbolName) : bool
    {
        return $this->isRegisteredNamespace(self::extractNameNamespace($symbolName));
    }
    public function isRegisteredNamespace(string $namespaceName) : bool
    {
        if ($this->containsGlobalNamespace) {
            return \true;
        }
        $originalNamespaceName = ltrim($namespaceName, '\\');
        $normalizedNamespaceName = strtolower($originalNamespaceName);
        foreach ($this->names as $excludedNamespaceName) {
            if ('' === $excludedNamespaceName || str_contains($normalizedNamespaceName, $excludedNamespaceName)) {
                return \true;
            }
        }
        foreach ($this->regexes as $excludedNamespace) {
            if (preg_match($excludedNamespace, $originalNamespaceName)) {
                return \true;
            }
        }
        return \false;
    }
    public function getNames() : array
    {
        return $this->names;
    }
    public function getRegexes() : array
    {
        return $this->regexes;
    }
    private static function extractNameNamespace(string $name) : string
    {
        $nameParts = explode('\\', $name);
        array_pop($nameParts);
        return [] === $nameParts ? '' : implode('\\', $nameParts);
    }
}
