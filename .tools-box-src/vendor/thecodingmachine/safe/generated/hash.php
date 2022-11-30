<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\HashException;
function hash_hkdf(string $algo, string $key, int $length = 0, string $info = "", string $salt = "") : string
{
    \error_clear_last();
    $safeResult = \hash_hkdf($algo, $key, $length, $info, $salt);
    if ($safeResult === \false) {
        throw HashException::createFromPhpError();
    }
    return $safeResult;
}
function hash_update_file(\HashContext $context, string $filename, ?\HashContext $stream_context = null) : void
{
    \error_clear_last();
    if ($stream_context !== null) {
        $safeResult = \hash_update_file($context, $filename, $stream_context);
    } else {
        $safeResult = \hash_update_file($context, $filename);
    }
    if ($safeResult === \false) {
        throw HashException::createFromPhpError();
    }
}
