<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\JsonException;
function json_encode($value, int $flags = 0, int $depth = 512) : string
{
    \error_clear_last();
    $safeResult = \json_encode($value, $flags, $depth);
    if ($safeResult === \false) {
        throw JsonException::createFromPhpError();
    }
    return $safeResult;
}
