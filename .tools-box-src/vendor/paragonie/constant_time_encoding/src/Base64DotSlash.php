<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\ParagonIE\ConstantTime;

abstract class Base64DotSlash extends Base64
{
    protected static function decode6Bits(int $src) : int
    {
        $ret = -1;
        $ret += (0x2d - $src & $src - 0x30) >> 8 & $src - 45;
        $ret += (0x40 - $src & $src - 0x5b) >> 8 & $src - 62;
        $ret += (0x60 - $src & $src - 0x7b) >> 8 & $src - 68;
        $ret += (0x2f - $src & $src - 0x3a) >> 8 & $src + 7;
        return $ret;
    }
    protected static function encode6Bits(int $src) : string
    {
        $src += 0x2e;
        $src += 0x2f - $src >> 8 & 17;
        $src += 0x5a - $src >> 8 & 6;
        $src -= 0x7a - $src >> 8 & 75;
        return \pack('C', $src);
    }
}
