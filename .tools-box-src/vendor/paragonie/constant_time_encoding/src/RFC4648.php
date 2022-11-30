<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\ParagonIE\ConstantTime;

use TypeError;
abstract class RFC4648
{
    public static function base64Encode(string $str) : string
    {
        return Base64::encode($str);
    }
    public static function base64Decode(string $str) : string
    {
        return Base64::decode($str, \true);
    }
    public static function base64UrlSafeEncode(string $str) : string
    {
        return Base64UrlSafe::encode($str);
    }
    public static function base64UrlSafeDecode(string $str) : string
    {
        return Base64UrlSafe::decode($str, \true);
    }
    public static function base32Encode(string $str) : string
    {
        return Base32::encodeUpper($str);
    }
    public static function base32Decode(string $str) : string
    {
        return Base32::decodeUpper($str, \true);
    }
    public static function base32HexEncode(string $str) : string
    {
        return Base32::encodeUpper($str);
    }
    public static function base32HexDecode(string $str) : string
    {
        return Base32::decodeUpper($str, \true);
    }
    public static function base16Encode(string $str) : string
    {
        return Hex::encodeUpper($str);
    }
    public static function base16Decode(string $str) : string
    {
        return Hex::decode($str, \true);
    }
}
