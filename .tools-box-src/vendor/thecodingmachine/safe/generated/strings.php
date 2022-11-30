<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\StringsException;
function convert_uudecode(string $string) : string
{
    \error_clear_last();
    $safeResult = \convert_uudecode($string);
    if ($safeResult === \false) {
        throw StringsException::createFromPhpError();
    }
    return $safeResult;
}
function hex2bin(string $string) : string
{
    \error_clear_last();
    $safeResult = \hex2bin($string);
    if ($safeResult === \false) {
        throw StringsException::createFromPhpError();
    }
    return $safeResult;
}
function md5_file(string $filename, bool $binary = \false) : string
{
    \error_clear_last();
    $safeResult = \md5_file($filename, $binary);
    if ($safeResult === \false) {
        throw StringsException::createFromPhpError();
    }
    return $safeResult;
}
function sha1_file(string $filename, bool $binary = \false) : string
{
    \error_clear_last();
    $safeResult = \sha1_file($filename, $binary);
    if ($safeResult === \false) {
        throw StringsException::createFromPhpError();
    }
    return $safeResult;
}
