<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\ParagonIE\ConstantTime;

use InvalidArgumentException;
use RangeException;
use TypeError;
abstract class Base64 implements EncoderInterface
{
    public static function encode(string $binString) : string
    {
        return static::doEncode($binString, \true);
    }
    public static function encodeUnpadded(string $src) : string
    {
        return static::doEncode($src, \false);
    }
    protected static function doEncode(string $src, bool $pad = \true) : string
    {
        $dest = '';
        $srcLen = Binary::safeStrlen($src);
        for ($i = 0; $i + 3 <= $srcLen; $i += 3) {
            $chunk = \unpack('C*', Binary::safeSubstr($src, $i, 3));
            $b0 = $chunk[1];
            $b1 = $chunk[2];
            $b2 = $chunk[3];
            $dest .= static::encode6Bits($b0 >> 2) . static::encode6Bits(($b0 << 4 | $b1 >> 4) & 63) . static::encode6Bits(($b1 << 2 | $b2 >> 6) & 63) . static::encode6Bits($b2 & 63);
        }
        if ($i < $srcLen) {
            $chunk = \unpack('C*', Binary::safeSubstr($src, $i, $srcLen - $i));
            $b0 = $chunk[1];
            if ($i + 1 < $srcLen) {
                $b1 = $chunk[2];
                $dest .= static::encode6Bits($b0 >> 2) . static::encode6Bits(($b0 << 4 | $b1 >> 4) & 63) . static::encode6Bits($b1 << 2 & 63);
                if ($pad) {
                    $dest .= '=';
                }
            } else {
                $dest .= static::encode6Bits($b0 >> 2) . static::encode6Bits($b0 << 4 & 63);
                if ($pad) {
                    $dest .= '==';
                }
            }
        }
        return $dest;
    }
    /**
    @psalm-suppress
    */
    public static function decode(string $encodedString, bool $strictPadding = \false) : string
    {
        $srcLen = Binary::safeStrlen($encodedString);
        if ($srcLen === 0) {
            return '';
        }
        if ($strictPadding) {
            if (($srcLen & 3) === 0) {
                if ($encodedString[$srcLen - 1] === '=') {
                    $srcLen--;
                    if ($encodedString[$srcLen - 1] === '=') {
                        $srcLen--;
                    }
                }
            }
            if (($srcLen & 3) === 1) {
                throw new RangeException('Incorrect padding');
            }
            if ($encodedString[$srcLen - 1] === '=') {
                throw new RangeException('Incorrect padding');
            }
        } else {
            $encodedString = \rtrim($encodedString, '=');
            $srcLen = Binary::safeStrlen($encodedString);
        }
        $err = 0;
        $dest = '';
        for ($i = 0; $i + 4 <= $srcLen; $i += 4) {
            $chunk = \unpack('C*', Binary::safeSubstr($encodedString, $i, 4));
            $c0 = static::decode6Bits($chunk[1]);
            $c1 = static::decode6Bits($chunk[2]);
            $c2 = static::decode6Bits($chunk[3]);
            $c3 = static::decode6Bits($chunk[4]);
            $dest .= \pack('CCC', ($c0 << 2 | $c1 >> 4) & 0xff, ($c1 << 4 | $c2 >> 2) & 0xff, ($c2 << 6 | $c3) & 0xff);
            $err |= ($c0 | $c1 | $c2 | $c3) >> 8;
        }
        if ($i < $srcLen) {
            $chunk = \unpack('C*', Binary::safeSubstr($encodedString, $i, $srcLen - $i));
            $c0 = static::decode6Bits($chunk[1]);
            if ($i + 2 < $srcLen) {
                $c1 = static::decode6Bits($chunk[2]);
                $c2 = static::decode6Bits($chunk[3]);
                $dest .= \pack('CC', ($c0 << 2 | $c1 >> 4) & 0xff, ($c1 << 4 | $c2 >> 2) & 0xff);
                $err |= ($c0 | $c1 | $c2) >> 8;
                if ($strictPadding) {
                    $err |= $c2 << 6 & 0xff;
                }
            } elseif ($i + 1 < $srcLen) {
                $c1 = static::decode6Bits($chunk[2]);
                $dest .= \pack('C', ($c0 << 2 | $c1 >> 4) & 0xff);
                $err |= ($c0 | $c1) >> 8;
                if ($strictPadding) {
                    $err |= $c1 << 4 & 0xff;
                }
            } elseif ($strictPadding) {
                $err |= 1;
            }
        }
        $check = $err === 0;
        if (!$check) {
            throw new RangeException('Base64::decode() only expects characters in the correct base64 alphabet');
        }
        return $dest;
    }
    public static function decodeNoPadding(string $encodedString) : string
    {
        $srcLen = Binary::safeStrlen($encodedString);
        if ($srcLen === 0) {
            return '';
        }
        if (($srcLen & 3) === 0) {
            if ($encodedString[$srcLen - 1] === '=') {
                throw new InvalidArgumentException("decodeNoPadding() doesn't tolerate padding");
            }
            if (($srcLen & 3) > 1) {
                if ($encodedString[$srcLen - 2] === '=') {
                    throw new InvalidArgumentException("decodeNoPadding() doesn't tolerate padding");
                }
            }
        }
        return static::decode($encodedString, \true);
    }
    protected static function decode6Bits(int $src) : int
    {
        $ret = -1;
        $ret += (0x40 - $src & $src - 0x5b) >> 8 & $src - 64;
        $ret += (0x60 - $src & $src - 0x7b) >> 8 & $src - 70;
        $ret += (0x2f - $src & $src - 0x3a) >> 8 & $src + 5;
        $ret += (0x2a - $src & $src - 0x2c) >> 8 & 63;
        $ret += (0x2e - $src & $src - 0x30) >> 8 & 64;
        return $ret;
    }
    protected static function encode6Bits(int $src) : string
    {
        $diff = 0x41;
        $diff += 25 - $src >> 8 & 6;
        $diff -= 51 - $src >> 8 & 75;
        $diff -= 61 - $src >> 8 & 15;
        $diff += 62 - $src >> 8 & 3;
        return \pack('C', $src + $diff);
    }
}
