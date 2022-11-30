<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\ParagonIE\ConstantTime;

use TypeError;
abstract class Encoding
{
    public static function base32Encode(string $str) : string
    {
        return Base32::encode($str);
    }
    public static function base32EncodeUpper(string $str) : string
    {
        return Base32::encodeUpper($str);
    }
    public static function base32Decode(string $str) : string
    {
        return Base32::decode($str);
    }
    public static function base32DecodeUpper(string $str) : string
    {
        return Base32::decodeUpper($str);
    }
    public static function base32HexEncode(string $str) : string
    {
        return Base32Hex::encode($str);
    }
    public static function base32HexEncodeUpper(string $str) : string
    {
        return Base32Hex::encodeUpper($str);
    }
    public static function base32HexDecode(string $str) : string
    {
        return Base32Hex::decode($str);
    }
    public static function base32HexDecodeUpper(string $str) : string
    {
        return Base32Hex::decodeUpper($str);
    }
    public static function base64Encode(string $str) : string
    {
        return Base64::encode($str);
    }
    public static function base64Decode(string $str) : string
    {
        return Base64::decode($str);
    }
    public static function base64EncodeDotSlash(string $str) : string
    {
        return Base64DotSlash::encode($str);
    }
    public static function base64DecodeDotSlash(string $str) : string
    {
        return Base64DotSlash::decode($str);
    }
    public static function base64EncodeDotSlashOrdered(string $str) : string
    {
        return Base64DotSlashOrdered::encode($str);
    }
    public static function base64DecodeDotSlashOrdered(string $str) : string
    {
        return Base64DotSlashOrdered::decode($str);
    }
    public static function hexEncode(string $bin_string) : string
    {
        return Hex::encode($bin_string);
    }
    public static function hexDecode(string $hex_string) : string
    {
        return Hex::decode($hex_string);
    }
    public static function hexEncodeUpper(string $bin_string) : string
    {
        return Hex::encodeUpper($bin_string);
    }
    public static function hexDecodeUpper(string $bin_string) : string
    {
        return Hex::decode($bin_string);
    }
}
