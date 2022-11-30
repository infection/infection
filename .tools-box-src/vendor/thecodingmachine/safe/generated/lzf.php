<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\LzfException;
function lzf_compress(string $data) : string
{
    \error_clear_last();
    $safeResult = \lzf_compress($data);
    if ($safeResult === \false) {
        throw LzfException::createFromPhpError();
    }
    return $safeResult;
}
function lzf_decompress(string $data) : string
{
    \error_clear_last();
    $safeResult = \lzf_decompress($data);
    if ($safeResult === \false) {
        throw LzfException::createFromPhpError();
    }
    return $safeResult;
}
