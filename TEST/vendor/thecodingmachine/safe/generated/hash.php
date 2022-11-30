<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\HashException;
function hash_hkdf(string $algo, string $key, int $length = 0, string $info = "", string $salt = "") : string
{
    \error_clear_last();
    $result = \hash_hkdf($algo, $key, $length, $info, $salt);
    if ($result === \false) {
        throw HashException::createFromPhpError();
    }
    return $result;
}
function hash_update_file(\HashContext $context, string $filename, ?\HashContext $stream_context = null) : void
{
    \error_clear_last();
    if ($stream_context !== null) {
        $result = \hash_update_file($context, $filename, $stream_context);
    } else {
        $result = \hash_update_file($context, $filename);
    }
    if ($result === \false) {
        throw HashException::createFromPhpError();
    }
}
