<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\ParagonIE\ConstantTime;

use InvalidArgumentException;
use RangeException;
use TypeError;
abstract class Base32 implements EncoderInterface
{
    public static function decode(string $encodedString, bool $strictPadding = \false) : string
    {
        return static::doDecode($encodedString, \false, $strictPadding);
    }
    public static function decodeUpper(string $src, bool $strictPadding = \false) : string
    {
        return static::doDecode($src, \true, $strictPadding);
    }
    public static function encode(string $binString) : string
    {
        return static::doEncode($binString, \false, \true);
    }
    public static function encodeUnpadded(string $src) : string
    {
        return static::doEncode($src, \false, \false);
    }
    public static function encodeUpper(string $src) : string
    {
        return static::doEncode($src, \true, \true);
    }
    public static function encodeUpperUnpadded(string $src) : string
    {
        return static::doEncode($src, \true, \false);
    }
    protected static function decode5Bits(int $src) : int
    {
        $ret = -1;
        $ret += (0x60 - $src & $src - 0x7b) >> 8 & $src - 96;
        $ret += (0x31 - $src & $src - 0x38) >> 8 & $src - 23;
        return $ret;
    }
    protected static function decode5BitsUpper(int $src) : int
    {
        $ret = -1;
        $ret += (0x40 - $src & $src - 0x5b) >> 8 & $src - 64;
        $ret += (0x31 - $src & $src - 0x38) >> 8 & $src - 23;
        return $ret;
    }
    protected static function encode5Bits(int $src) : string
    {
        $diff = 0x61;
        $diff -= 25 - $src >> 8 & 73;
        return \pack('C', $src + $diff);
    }
    protected static function encode5BitsUpper(int $src) : string
    {
        $diff = 0x41;
        $diff -= 25 - $src >> 8 & 41;
        return \pack('C', $src + $diff);
    }
    public static function decodeNoPadding(string $encodedString, bool $upper = \false) : string
    {
        $srcLen = Binary::safeStrlen($encodedString);
        if ($srcLen === 0) {
            return '';
        }
        if (($srcLen & 7) === 0) {
            for ($j = 0; $j < 7 && $j < $srcLen; ++$j) {
                if ($encodedString[$srcLen - $j - 1] === '=') {
                    throw new InvalidArgumentException("decodeNoPadding() doesn't tolerate padding");
                }
            }
        }
        return static::doDecode($encodedString, $upper, \true);
    }
    /**
    @psalm-suppress
    */
    protected static function doDecode(string $src, bool $upper = \false, bool $strictPadding = \false) : string
    {
        $method = $upper ? 'decode5BitsUpper' : 'decode5Bits';
        $srcLen = Binary::safeStrlen($src);
        if ($srcLen === 0) {
            return '';
        }
        if ($strictPadding) {
            if (($srcLen & 7) === 0) {
                for ($j = 0; $j < 7; ++$j) {
                    if ($src[$srcLen - 1] === '=') {
                        $srcLen--;
                    } else {
                        break;
                    }
                }
            }
            if (($srcLen & 7) === 1) {
                throw new RangeException('Incorrect padding');
            }
        } else {
            $src = \rtrim($src, '=');
            $srcLen = Binary::safeStrlen($src);
        }
        $err = 0;
        $dest = '';
        for ($i = 0; $i + 8 <= $srcLen; $i += 8) {
            $chunk = \unpack('C*', Binary::safeSubstr($src, $i, 8));
            $c0 = static::$method($chunk[1]);
            $c1 = static::$method($chunk[2]);
            $c2 = static::$method($chunk[3]);
            $c3 = static::$method($chunk[4]);
            $c4 = static::$method($chunk[5]);
            $c5 = static::$method($chunk[6]);
            $c6 = static::$method($chunk[7]);
            $c7 = static::$method($chunk[8]);
            $dest .= \pack('CCCCC', ($c0 << 3 | $c1 >> 2) & 0xff, ($c1 << 6 | $c2 << 1 | $c3 >> 4) & 0xff, ($c3 << 4 | $c4 >> 1) & 0xff, ($c4 << 7 | $c5 << 2 | $c6 >> 3) & 0xff, ($c6 << 5 | $c7) & 0xff);
            $err |= ($c0 | $c1 | $c2 | $c3 | $c4 | $c5 | $c6 | $c7) >> 8;
        }
        if ($i < $srcLen) {
            $chunk = \unpack('C*', Binary::safeSubstr($src, $i, $srcLen - $i));
            $c0 = static::$method($chunk[1]);
            if ($i + 6 < $srcLen) {
                $c1 = static::$method($chunk[2]);
                $c2 = static::$method($chunk[3]);
                $c3 = static::$method($chunk[4]);
                $c4 = static::$method($chunk[5]);
                $c5 = static::$method($chunk[6]);
                $c6 = static::$method($chunk[7]);
                $dest .= \pack('CCCC', ($c0 << 3 | $c1 >> 2) & 0xff, ($c1 << 6 | $c2 << 1 | $c3 >> 4) & 0xff, ($c3 << 4 | $c4 >> 1) & 0xff, ($c4 << 7 | $c5 << 2 | $c6 >> 3) & 0xff);
                $err |= ($c0 | $c1 | $c2 | $c3 | $c4 | $c5 | $c6) >> 8;
                if ($strictPadding) {
                    $err |= $c6 << 5 & 0xff;
                }
            } elseif ($i + 5 < $srcLen) {
                $c1 = static::$method($chunk[2]);
                $c2 = static::$method($chunk[3]);
                $c3 = static::$method($chunk[4]);
                $c4 = static::$method($chunk[5]);
                $c5 = static::$method($chunk[6]);
                $dest .= \pack('CCCC', ($c0 << 3 | $c1 >> 2) & 0xff, ($c1 << 6 | $c2 << 1 | $c3 >> 4) & 0xff, ($c3 << 4 | $c4 >> 1) & 0xff, ($c4 << 7 | $c5 << 2) & 0xff);
                $err |= ($c0 | $c1 | $c2 | $c3 | $c4 | $c5) >> 8;
            } elseif ($i + 4 < $srcLen) {
                $c1 = static::$method($chunk[2]);
                $c2 = static::$method($chunk[3]);
                $c3 = static::$method($chunk[4]);
                $c4 = static::$method($chunk[5]);
                $dest .= \pack('CCC', ($c0 << 3 | $c1 >> 2) & 0xff, ($c1 << 6 | $c2 << 1 | $c3 >> 4) & 0xff, ($c3 << 4 | $c4 >> 1) & 0xff);
                $err |= ($c0 | $c1 | $c2 | $c3 | $c4) >> 8;
                if ($strictPadding) {
                    $err |= $c4 << 7 & 0xff;
                }
            } elseif ($i + 3 < $srcLen) {
                $c1 = static::$method($chunk[2]);
                $c2 = static::$method($chunk[3]);
                $c3 = static::$method($chunk[4]);
                $dest .= \pack('CC', ($c0 << 3 | $c1 >> 2) & 0xff, ($c1 << 6 | $c2 << 1 | $c3 >> 4) & 0xff);
                $err |= ($c0 | $c1 | $c2 | $c3) >> 8;
                if ($strictPadding) {
                    $err |= $c3 << 4 & 0xff;
                }
            } elseif ($i + 2 < $srcLen) {
                $c1 = static::$method($chunk[2]);
                $c2 = static::$method($chunk[3]);
                $dest .= \pack('CC', ($c0 << 3 | $c1 >> 2) & 0xff, ($c1 << 6 | $c2 << 1) & 0xff);
                $err |= ($c0 | $c1 | $c2) >> 8;
                if ($strictPadding) {
                    $err |= $c2 << 6 & 0xff;
                }
            } elseif ($i + 1 < $srcLen) {
                $c1 = static::$method($chunk[2]);
                $dest .= \pack('C', ($c0 << 3 | $c1 >> 2) & 0xff);
                $err |= ($c0 | $c1) >> 8;
                if ($strictPadding) {
                    $err |= $c1 << 6 & 0xff;
                }
            } else {
                $dest .= \pack('C', $c0 << 3 & 0xff);
                $err |= $c0 >> 8;
            }
        }
        $check = $err === 0;
        if (!$check) {
            throw new RangeException('Base32::doDecode() only expects characters in the correct base32 alphabet');
        }
        return $dest;
    }
    protected static function doEncode(string $src, bool $upper = \false, $pad = \true) : string
    {
        $method = $upper ? 'encode5BitsUpper' : 'encode5Bits';
        $dest = '';
        $srcLen = Binary::safeStrlen($src);
        for ($i = 0; $i + 5 <= $srcLen; $i += 5) {
            $chunk = \unpack('C*', Binary::safeSubstr($src, $i, 5));
            $b0 = $chunk[1];
            $b1 = $chunk[2];
            $b2 = $chunk[3];
            $b3 = $chunk[4];
            $b4 = $chunk[5];
            $dest .= static::$method($b0 >> 3 & 31) . static::$method(($b0 << 2 | $b1 >> 6) & 31) . static::$method($b1 >> 1 & 31) . static::$method(($b1 << 4 | $b2 >> 4) & 31) . static::$method(($b2 << 1 | $b3 >> 7) & 31) . static::$method($b3 >> 2 & 31) . static::$method(($b3 << 3 | $b4 >> 5) & 31) . static::$method($b4 & 31);
        }
        if ($i < $srcLen) {
            $chunk = \unpack('C*', Binary::safeSubstr($src, $i, $srcLen - $i));
            $b0 = $chunk[1];
            if ($i + 3 < $srcLen) {
                $b1 = $chunk[2];
                $b2 = $chunk[3];
                $b3 = $chunk[4];
                $dest .= static::$method($b0 >> 3 & 31) . static::$method(($b0 << 2 | $b1 >> 6) & 31) . static::$method($b1 >> 1 & 31) . static::$method(($b1 << 4 | $b2 >> 4) & 31) . static::$method(($b2 << 1 | $b3 >> 7) & 31) . static::$method($b3 >> 2 & 31) . static::$method($b3 << 3 & 31);
                if ($pad) {
                    $dest .= '=';
                }
            } elseif ($i + 2 < $srcLen) {
                $b1 = $chunk[2];
                $b2 = $chunk[3];
                $dest .= static::$method($b0 >> 3 & 31) . static::$method(($b0 << 2 | $b1 >> 6) & 31) . static::$method($b1 >> 1 & 31) . static::$method(($b1 << 4 | $b2 >> 4) & 31) . static::$method($b2 << 1 & 31);
                if ($pad) {
                    $dest .= '===';
                }
            } elseif ($i + 1 < $srcLen) {
                $b1 = $chunk[2];
                $dest .= static::$method($b0 >> 3 & 31) . static::$method(($b0 << 2 | $b1 >> 6) & 31) . static::$method($b1 >> 1 & 31) . static::$method($b1 << 4 & 31);
                if ($pad) {
                    $dest .= '====';
                }
            } else {
                $dest .= static::$method($b0 >> 3 & 31) . static::$method($b0 << 2 & 31);
                if ($pad) {
                    $dest .= '======';
                }
            }
        }
        return $dest;
    }
}
