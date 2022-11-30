<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\MiscException;
function sleep(int $seconds) : int
{
    \error_clear_last();
    $safeResult = \sleep($seconds);
    if ($safeResult === \false) {
        throw MiscException::createFromPhpError();
    }
    return $safeResult;
}
