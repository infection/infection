<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\ParagonIE\ConstantTime;

use RangeException;
use TypeError;
abstract class Hex implements EncoderInterface
{
    public static function encode(string $binString) : string
    {
        $hex = '';
        $len = Binary::safeStrlen($binString);
        for ($i = 0; $i < $len; ++$i) {
            $chunk = \unpack('C', $binString[$i]);
            $c = $chunk[1] & 0xf;
            $b = $chunk[1] >> 4;
            $hex .= \pack('CC', 87 + $b + ($b - 10 >> 8 & ~38), 87 + $c + ($c - 10 >> 8 & ~38));
        }
        return $hex;
    }
    public static function encodeUpper(string $binString) : string
    {
        $hex = '';
        $len = Binary::safeStrlen($binString);
        for ($i = 0; $i < $len; ++$i) {
            $chunk = \unpack('C', $binString[$i]);
            $c = $chunk[1] & 0xf;
            $b = $chunk[1] >> 4;
            $hex .= \pack('CC', 55 + $b + ($b - 10 >> 8 & ~6), 55 + $c + ($c - 10 >> 8 & ~6));
        }
        return $hex;
    }
    public static function decode(string $encodedString, bool $strictPadding = \false) : string
    {
        $hex_pos = 0;
        $bin = '';
        $c_acc = 0;
        $hex_len = Binary::safeStrlen($encodedString);
        $state = 0;
        if (($hex_len & 1) !== 0) {
            if ($strictPadding) {
                throw new RangeException('Expected an even number of hexadecimal characters');
            } else {
                $encodedString = '0' . $encodedString;
                ++$hex_len;
            }
        }
        $chunk = \unpack('C*', $encodedString);
        while ($hex_pos < $hex_len) {
            ++$hex_pos;
            $c = $chunk[$hex_pos];
            $c_num = $c ^ 48;
            $c_num0 = $c_num - 10 >> 8;
            $c_alpha = ($c & ~32) - 55;
            $c_alpha0 = ($c_alpha - 10 ^ $c_alpha - 16) >> 8;
            if (($c_num0 | $c_alpha0) === 0) {
                throw new RangeException('Expected hexadecimal character');
            }
            $c_val = $c_num0 & $c_num | $c_alpha & $c_alpha0;
            if ($state === 0) {
                $c_acc = $c_val * 16;
            } else {
                $bin .= \pack('C', $c_acc | $c_val);
            }
            $state ^= 1;
        }
        return $bin;
    }
}
