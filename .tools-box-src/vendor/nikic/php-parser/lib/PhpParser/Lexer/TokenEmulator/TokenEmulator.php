<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Lexer\TokenEmulator;

abstract class TokenEmulator
{
    public abstract function getPhpVersion() : string;
    public abstract function isEmulationNeeded(string $code) : bool;
    public abstract function emulate(string $code, array $tokens) : array;
    public abstract function reverseEmulate(string $code, array $tokens) : array;
    public function preprocessCode(string $code, array &$patches) : string
    {
        return $code;
    }
}
