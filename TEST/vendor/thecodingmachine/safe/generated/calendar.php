<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\CalendarException;
function unixtojd(int $timestamp = null) : int
{
    \error_clear_last();
    if ($timestamp !== null) {
        $result = \unixtojd($timestamp);
    } else {
        $result = \unixtojd();
    }
    if ($result === \false) {
        throw CalendarException::createFromPhpError();
    }
    return $result;
}
