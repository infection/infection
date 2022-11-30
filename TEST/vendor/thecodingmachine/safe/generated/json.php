<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\JsonException;
function json_encode($value, int $flags = 0, int $depth = 512) : string
{
    \error_clear_last();
    $result = \json_encode($value, $flags, $depth);
    if ($result === \false) {
        throw JsonException::createFromPhpError();
    }
    return $result;
}
