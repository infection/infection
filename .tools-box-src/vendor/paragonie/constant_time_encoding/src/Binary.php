<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\ParagonIE\ConstantTime;

use TypeError;
abstract class Binary
{
    /**
    @ref
    */
    public static function safeStrlen(string $str) : int
    {
        if (\function_exists('mb_strlen')) {
            /**
            @psalm-suppress */
            return (int) \mb_strlen($str, '8bit');
        } else {
            return \strlen($str);
        }
    }
    /**
    @ref
    */
    public static function safeSubstr(string $str, int $start = 0, $length = null) : string
    {
        if ($length === 0) {
            return '';
        }
        if (\function_exists('mb_substr')) {
            return \mb_substr($str, $start, $length, '8bit');
        }
        if ($length !== null) {
            return \substr($str, $start, $length);
        } else {
            return \substr($str, $start);
        }
    }
}
