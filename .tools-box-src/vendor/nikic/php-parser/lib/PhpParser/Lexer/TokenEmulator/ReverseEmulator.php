<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Lexer\TokenEmulator;

final class ReverseEmulator extends TokenEmulator
{
    private $emulator;
    public function __construct(TokenEmulator $emulator)
    {
        $this->emulator = $emulator;
    }
    public function getPhpVersion() : string
    {
        return $this->emulator->getPhpVersion();
    }
    public function isEmulationNeeded(string $code) : bool
    {
        return $this->emulator->isEmulationNeeded($code);
    }
    public function emulate(string $code, array $tokens) : array
    {
        return $this->emulator->reverseEmulate($code, $tokens);
    }
    public function reverseEmulate(string $code, array $tokens) : array
    {
        return $this->emulator->emulate($code, $tokens);
    }
    public function preprocessCode(string $code, array &$patches) : string
    {
        return $code;
    }
}
