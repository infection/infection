<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\ParagonIE\ConstantTime;

abstract class Base64UrlSafe extends Base64
{
    protected static function decode6Bits(int $src) : int
    {
        $ret = -1;
        $ret += (0x40 - $src & $src - 0x5b) >> 8 & $src - 64;
        $ret += (0x60 - $src & $src - 0x7b) >> 8 & $src - 70;
        $ret += (0x2f - $src & $src - 0x3a) >> 8 & $src + 5;
        $ret += (0x2c - $src & $src - 0x2e) >> 8 & 63;
        $ret += (0x5e - $src & $src - 0x60) >> 8 & 64;
        return $ret;
    }
    protected static function encode6Bits(int $src) : string
    {
        $diff = 0x41;
        $diff += 25 - $src >> 8 & 6;
        $diff -= 51 - $src >> 8 & 75;
        $diff -= 61 - $src >> 8 & 13;
        $diff += 62 - $src >> 8 & 49;
        return \pack('C', $src + $diff);
    }
}
