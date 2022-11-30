<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol;

use InvalidArgumentException;
use function array_flip;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_pop;
use function array_unique;
use function explode;
use function implode;
use function ltrim;
use function _HumbugBoxb47773b41c19\Safe\preg_match;
use function strtolower;
use function trim;
final class SymbolRegistry
{
    private array $names;
    public static function create(array $names = [], array $regexes = []) : self
    {
        return new self(self::normalizeNames($names), array_unique($regexes), \false);
    }
    public static function createForConstants(array $names = [], array $regexes = []) : self
    {
        return new self(self::normalizeConstantNames($names), array_unique($regexes), \true);
    }
    private function __construct(array $names, private array $regexes, private bool $constants)
    {
        $this->names = array_flip($names);
        if (array_key_exists('', $this->names)) {
            throw new InvalidArgumentException('Cannot register "" as a symbol name.');
        }
        if (array_key_exists('', array_flip($regexes))) {
            throw new InvalidArgumentException('Cannot register "" as a symbol regex.');
        }
    }
    public function matches(string $symbol) : bool
    {
        $originalSymbol = ltrim($symbol, '\\');
        $symbol = $this->constants ? self::lowerCaseConstantName($originalSymbol) : strtolower($originalSymbol);
        if (array_key_exists($symbol, $this->names)) {
            return \true;
        }
        foreach ($this->regexes as $regex) {
            if (preg_match($regex, $originalSymbol)) {
                return \true;
            }
        }
        return \false;
    }
    public function merge(self $registry) : self
    {
        if ($this->constants !== $registry->constants) {
            throw new InvalidArgumentException('Cannot merge registries of different symbol types');
        }
        $args = [[...$this->getNames(), ...$registry->getNames()], [...$this->getRegexes(), ...$registry->getRegexes()]];
        return $this->constants ? self::createForConstants(...$args) : self::create(...$args);
    }
    public function getNames() : array
    {
        return array_keys($this->names);
    }
    /**
    @erturn
    */
    public function getRegexes() : array
    {
        return $this->regexes;
    }
    private static function normalizeNames(array $names) : array
    {
        return array_map(static fn(string $name) => strtolower(self::normalizeName($name)), $names);
    }
    private static function normalizeConstantNames(array $names) : array
    {
        return array_map(static fn(string $name) => self::lowerCaseConstantName(self::normalizeName($name)), $names);
    }
    private static function normalizeName(string $name) : string
    {
        return trim($name, '\\ ');
    }
    private static function lowerCaseConstantName(string $name) : string
    {
        $parts = explode('\\', $name);
        $lastPart = array_pop($parts);
        $parts = array_map('strtolower', $parts);
        $parts[] = $lastPart;
        return implode('\\', $parts);
    }
}
