<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\DatetimeException;
function date_parse_from_format(string $format, string $datetime) : array
{
    \error_clear_last();
    $result = \date_parse_from_format($format, $datetime);
    if ($result === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $result;
}
function date_parse(string $datetime) : array
{
    \error_clear_last();
    $result = \date_parse($datetime);
    if ($result === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $result;
}
function date_sun_info(int $timestamp, float $latitude, float $longitude) : array
{
    \error_clear_last();
    $result = \date_sun_info($timestamp, $latitude, $longitude);
    if ($result === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $result;
}
function date_sunrise(int $timestamp, int $returnFormat = \SUNFUNCS_RET_STRING, float $latitude = null, float $longitude = null, float $zenith = null, float $utcOffset = null)
{
    \error_clear_last();
    if ($utcOffset !== null) {
        $result = \date_sunrise($timestamp, $returnFormat, $latitude, $longitude, $zenith, $utcOffset);
    } elseif ($zenith !== null) {
        $result = \date_sunrise($timestamp, $returnFormat, $latitude, $longitude, $zenith);
    } elseif ($longitude !== null) {
        $result = \date_sunrise($timestamp, $returnFormat, $latitude, $longitude);
    } elseif ($latitude !== null) {
        $result = \date_sunrise($timestamp, $returnFormat, $latitude);
    } else {
        $result = \date_sunrise($timestamp, $returnFormat);
    }
    if ($result === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $result;
}
function date_sunset(int $timestamp, int $returnFormat = \SUNFUNCS_RET_STRING, float $latitude = null, float $longitude = null, float $zenith = null, float $utcOffset = null)
{
    \error_clear_last();
    if ($utcOffset !== null) {
        $result = \date_sunset($timestamp, $returnFormat, $latitude, $longitude, $zenith, $utcOffset);
    } elseif ($zenith !== null) {
        $result = \date_sunset($timestamp, $returnFormat, $latitude, $longitude, $zenith);
    } elseif ($longitude !== null) {
        $result = \date_sunset($timestamp, $returnFormat, $latitude, $longitude);
    } elseif ($latitude !== null) {
        $result = \date_sunset($timestamp, $returnFormat, $latitude);
    } else {
        $result = \date_sunset($timestamp, $returnFormat);
    }
    if ($result === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $result;
}
function date(string $format, int $timestamp = null) : string
{
    \error_clear_last();
    if ($timestamp !== null) {
        $result = \date($format, $timestamp);
    } else {
        $result = \date($format);
    }
    if ($result === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $result;
}
function gmmktime(int $hour, int $minute = null, int $second = null, int $month = null, int $day = null, int $year = null) : int
{
    \error_clear_last();
    if ($year !== null) {
        $result = \gmmktime($hour, $minute, $second, $month, $day, $year);
    } elseif ($day !== null) {
        $result = \gmmktime($hour, $minute, $second, $month, $day);
    } elseif ($month !== null) {
        $result = \gmmktime($hour, $minute, $second, $month);
    } elseif ($second !== null) {
        $result = \gmmktime($hour, $minute, $second);
    } elseif ($minute !== null) {
        $result = \gmmktime($hour, $minute);
    } else {
        $result = \gmmktime($hour);
    }
    if ($result === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $result;
}
function gmstrftime(string $format, int $timestamp = null) : string
{
    \error_clear_last();
    if ($timestamp !== null) {
        $result = \gmstrftime($format, $timestamp);
    } else {
        $result = \gmstrftime($format);
    }
    if ($result === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $result;
}
function idate(string $format, int $timestamp = null) : int
{
    \error_clear_last();
    if ($timestamp !== null) {
        $result = \idate($format, $timestamp);
    } else {
        $result = \idate($format);
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
        $result = \mktime($hour, $minute, $second, $month, $day, $year);
    } elseif ($day !== null) {
        $result = \mktime($hour, $minute, $second, $month, $day);
    } elseif ($month !== null) {
        $result = \mktime($hour, $minute, $second, $month);
    } elseif ($second !== null) {
        $result = \mktime($hour, $minute, $second);
    } elseif ($minute !== null) {
        $result = \mktime($hour, $minute);
    } else {
        $result = \mktime($hour);
    }
    if ($result === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $result;
}
function strftime(string $format, int $timestamp = null) : string
{
    \error_clear_last();
    if ($timestamp !== null) {
        $result = \strftime($format, $timestamp);
    } else {
        $result = \strftime($format);
    }
    if ($result === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $result;
}
function strptime(string $timestamp, string $format) : array
{
    \error_clear_last();
    $result = \strptime($timestamp, $format);
    if ($result === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $result;
}
function strtotime(string $datetime, int $baseTimestamp = null) : int
{
    \error_clear_last();
    if ($baseTimestamp !== null) {
        $result = \strtotime($datetime, $baseTimestamp);
    } else {
        $result = \strtotime($datetime);
    }
    if ($result === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $result;
}
function timezone_name_from_abbr(string $abbr, int $utcOffset = -1, int $isDST = -1) : string
{
    \error_clear_last();
    $result = \timezone_name_from_abbr($abbr, $utcOffset, $isDST);
    if ($result === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $result;
}
