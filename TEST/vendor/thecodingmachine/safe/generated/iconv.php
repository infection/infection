<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\IconvException;
function iconv_get_encoding(string $type = "all")
{
    \error_clear_last();
    $result = \iconv_get_encoding($type);
    if ($result === \false) {
        throw IconvException::createFromPhpError();
    }
    return $result;
}
function iconv_set_encoding(string $type, string $encoding) : void
{
    \error_clear_last();
    $result = \iconv_set_encoding($type, $encoding);
    if ($result === \false) {
        throw IconvException::createFromPhpError();
    }
}
function iconv(string $from_encoding, string $to_encoding, string $string) : string
{
    \error_clear_last();
    $result = \iconv($from_encoding, $to_encoding, $string);
    if ($result === \false) {
        throw IconvException::createFromPhpError();
    }
    return $result;
}
