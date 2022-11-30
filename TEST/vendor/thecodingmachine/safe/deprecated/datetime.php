<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\DatetimeException;
function gmdate(string $format, int $timestamp = null) : string
{
    \error_clear_last();
    if ($timestamp !== null) {
        $result = \gmdate($format, $timestamp);
    } else {
        $result = \gmdate($format);
    }
    if ($result === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $result;
}
