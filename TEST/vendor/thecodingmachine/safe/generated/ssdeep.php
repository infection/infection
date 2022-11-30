<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\SsdeepException;
function ssdeep_fuzzy_compare(string $signature1, string $signature2) : int
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ssdeep_fuzzy_compare($signature1, $signature2);
    if ($result === \false) {
        throw SsdeepException::createFromPhpError();
    }
    return $result;
}
function ssdeep_fuzzy_hash_filename(string $file_name) : string
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ssdeep_fuzzy_hash_filename($file_name);
    if ($result === \false) {
        throw SsdeepException::createFromPhpError();
    }
    return $result;
}
function ssdeep_fuzzy_hash(string $to_hash) : string
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ssdeep_fuzzy_hash($to_hash);
    if ($result === \false) {
        throw SsdeepException::createFromPhpError();
    }
    return $result;
}
