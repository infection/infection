<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\StringsException;
function convert_uudecode(string $string) : string
{
    \error_clear_last();
    $result = \convert_uudecode($string);
    if ($result === \false) {
        throw StringsException::createFromPhpError();
    }
    return $result;
}
function hex2bin(string $string) : string
{
    \error_clear_last();
    $result = \hex2bin($string);
    if ($result === \false) {
        throw StringsException::createFromPhpError();
    }
    return $result;
}
function md5_file(string $filename, bool $binary = \false) : string
{
    \error_clear_last();
    $result = \md5_file($filename, $binary);
    if ($result === \false) {
        throw StringsException::createFromPhpError();
    }
    return $result;
}
function sha1_file(string $filename, bool $binary = \false) : string
{
    \error_clear_last();
    $result = \sha1_file($filename, $binary);
    if ($result === \false) {
        throw StringsException::createFromPhpError();
    }
    return $result;
}
