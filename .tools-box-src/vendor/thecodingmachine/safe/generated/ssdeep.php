<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\SsdeepException;
function ssdeep_fuzzy_compare(string $signature1, string $signature2) : int
{
    \error_clear_last();
    $safeResult = \_HumbugBoxb47773b41c19\ssdeep_fuzzy_compare($signature1, $signature2);
    if ($safeResult === \false) {
        throw SsdeepException::createFromPhpError();
    }
    return $safeResult;
}
function ssdeep_fuzzy_hash_filename(string $file_name) : string
{
    \error_clear_last();
    $safeResult = \_HumbugBoxb47773b41c19\ssdeep_fuzzy_hash_filename($file_name);
    if ($safeResult === \false) {
        throw SsdeepException::createFromPhpError();
    }
    return $safeResult;
}
function ssdeep_fuzzy_hash(string $to_hash) : string
{
    \error_clear_last();
    $safeResult = \_HumbugBoxb47773b41c19\ssdeep_fuzzy_hash($to_hash);
    if ($safeResult === \false) {
        throw SsdeepException::createFromPhpError();
    }
    return $safeResult;
}
