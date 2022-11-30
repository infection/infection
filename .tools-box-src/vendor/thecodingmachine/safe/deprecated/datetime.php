<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\DatetimeException;
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
function mktime(int $hour, int $minute = null, int $second = null, int $month = null, int $day = null, int $year = null) : int
{
    \error_clear_last();
    if ($year !== null) {
        $safeResult = \mktime($hour, $minute, $second, $month, $day, $year);
    } elseif ($day !== null) {
        $safeResult = \mktime($hour, $minute, $second, $month, $day);
    } elseif ($month !== null) {
        $safeResult = \mktime($hour, $minute, $second, $month);
    } elseif ($second !== null) {
        $safeResult = \mktime($hour, $minute, $second);
    } elseif ($minute !== null) {
        $safeResult = \mktime($hour, $minute);
    } else {
        $safeResult = \mktime($hour);
    }
    if ($safeResult === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $safeResult;
}
