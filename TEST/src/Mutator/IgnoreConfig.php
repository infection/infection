<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator;

use function array_key_exists;
use const FNM_NOESCAPE;
use function fnmatch;
use function _HumbugBox9658796bb9f0\Safe\array_flip;
class IgnoreConfig
{
    private array $hashtable = [];
    public function __construct(private array $patterns)
    {
        if ($patterns !== []) {
            $this->hashtable = array_flip($patterns);
        }
    }
    public function isIgnored(string $class, string $method, ?int $lineNumber) : bool
    {
        if ($this->patterns === []) {
            return \false;
        }
        if (array_key_exists($class, $this->hashtable)) {
            return \true;
        }
        $classMethod = $class . '::' . $method;
        if (array_key_exists($classMethod, $this->hashtable)) {
            return \true;
        }
        foreach ($this->patterns as $ignorePattern) {
            if (fnmatch($ignorePattern, $class, FNM_NOESCAPE) || fnmatch($ignorePattern, $classMethod, FNM_NOESCAPE) || $lineNumber !== null && fnmatch($ignorePattern, $classMethod . '::' . $lineNumber, FNM_NOESCAPE)) {
                return \true;
            }
        }
        return \false;
    }
}
