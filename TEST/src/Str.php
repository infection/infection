<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection;

use function array_values;
use function count;
use function explode;
use function implode;
use const PHP_EOL;
use function _HumbugBox9658796bb9f0\Safe\mb_convert_encoding;
use function str_replace;
use function trim;
final class Str
{
    use CannotBeInstantiated;
    public static function trimLineReturns(string $string) : string
    {
        $lines = explode("\n", str_replace("\r\n", "\n", $string));
        $linesCount = count($lines);
        for ($i = 0; $i < $linesCount; ++$i) {
            $line = $lines[$i];
            if (trim($line) === '') {
                unset($lines[$i]);
                continue;
            }
            break;
        }
        $lines = array_values($lines);
        $linesCount = count($lines);
        for ($i = $linesCount - 1; $i >= 0; --$i) {
            $line = $lines[$i];
            if (trim($line) === '') {
                unset($lines[$i]);
                continue;
            }
            break;
        }
        return implode(PHP_EOL, $lines);
    }
    public static function convertToUtf8(string $string) : string
    {
        $utf8String = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        return $utf8String;
    }
}
