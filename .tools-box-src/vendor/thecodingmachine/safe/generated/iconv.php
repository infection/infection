<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\IconvException;
function iconv_get_encoding(string $type = "all")
{
    \error_clear_last();
    $safeResult = \iconv_get_encoding($type);
    if ($safeResult === \false) {
        throw IconvException::createFromPhpError();
    }
    return $safeResult;
}
function iconv_set_encoding(string $type, string $encoding) : void
{
    \error_clear_last();
    $safeResult = \iconv_set_encoding($type, $encoding);
    if ($safeResult === \false) {
        throw IconvException::createFromPhpError();
    }
}
function iconv(string $from_encoding, string $to_encoding, string $string) : string
{
    \error_clear_last();
    $safeResult = \iconv($from_encoding, $to_encoding, $string);
    if ($safeResult === \false) {
        throw IconvException::createFromPhpError();
    }
    return $safeResult;
}
