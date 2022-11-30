<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console;

use function array_map;
use function explode;
use function implode;
use function str_replace;
use const PHP_EOL;
final class DisplayNormalizer
{
    private function __construct()
    {
    }
    public static function removeTrailingSpaces(string $display, callable ...$extraNormalizers) : string
    {
        $display = str_replace(PHP_EOL, "\n", $display);
        $lines = explode("\n", $display);
        $trimmedLines = array_map('rtrim', $lines);
        $normalizedDisplay = implode("\n", $trimmedLines);
        return \array_reduce($extraNormalizers, static fn(string $display, $extraNormalizer): string => $extraNormalizer($display), $normalizedDisplay);
    }
}
