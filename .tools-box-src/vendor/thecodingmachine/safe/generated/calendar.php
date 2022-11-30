<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\CalendarException;
function unixtojd(int $timestamp = null) : int
{
    \error_clear_last();
    if ($timestamp !== null) {
        $safeResult = \unixtojd($timestamp);
    } else {
        $safeResult = \unixtojd();
    }
    if ($safeResult === \false) {
        throw CalendarException::createFromPhpError();
    }
    return $safeResult;
}
