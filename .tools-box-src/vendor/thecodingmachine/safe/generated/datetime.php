<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\DatetimeException;
function date_parse_from_format(string $format, string $datetime) : ?array
{
    \error_clear_last();
    $safeResult = \date_parse_from_format($format, $datetime);
    if ($safeResult === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $safeResult;
}
function date_parse(string $datetime) : ?array
{
    \error_clear_last();
    $safeResult = \date_parse($datetime);
    if ($safeResult === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $safeResult;
}
function date_sun_info(int $timestamp, float $latitude, float $longitude) : array
{
    \error_clear_last();
    $safeResult = \date_sun_info($timestamp, $latitude, $longitude);
    if ($safeResult === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $safeResult;
}
function date_sunrise(int $timestamp, int $returnFormat = \SUNFUNCS_RET_STRING, float $latitude = null, float $longitude = null, float $zenith = null, float $utcOffset = null)
{
    \error_clear_last();
    if ($utcOffset !== null) {
        $safeResult = \date_sunrise($timestamp, $returnFormat, $latitude, $longitude, $zenith, $utcOffset);
    } elseif ($zenith !== null) {
        $safeResult = \date_sunrise($timestamp, $returnFormat, $latitude, $longitude, $zenith);
    } elseif ($longitude !== null) {
        $safeResult = \date_sunrise($timestamp, $returnFormat, $latitude, $longitude);
    } elseif ($latitude !== null) {
        $safeResult = \date_sunrise($timestamp, $returnFormat, $latitude);
    } else {
        $safeResult = \date_sunrise($timestamp, $returnFormat);
    }
    if ($safeResult === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $safeResult;
}
function date_sunset(int $timestamp, int $returnFormat = \SUNFUNCS_RET_STRING, float $latitude = null, float $longitude = null, float $zenith = null, float $utcOffset = null)
{
    \error_clear_last();
    if ($utcOffset !== null) {
        $safeResult = \date_sunset($timestamp, $returnFormat, $latitude, $longitude, $zenith, $utcOffset);
    } elseif ($zenith !== null) {
        $safeResult = \date_sunset($timestamp, $returnFormat, $latitude, $longitude, $zenith);
    } elseif ($longitude !== null) {
        $safeResult = \date_sunset($timestamp, $returnFormat, $latitude, $longitude);
    } elseif ($latitude !== null) {
        $safeResult = \date_sunset($timestamp, $returnFormat, $latitude);
    } else {
        $safeResult = \date_sunset($timestamp, $returnFormat);
    }
    if ($safeResult === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $safeResult;
}
function date(string $format, int $timestamp = null) : string
{
    \error_clear_last();
    if ($timestamp !== null) {
        $safeResult = \date($format, $timestamp);
    } else {
        $safeResult = \date($format);
    }
    if ($safeResult === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $safeResult;
}
function gmmktime(int $hour, int $minute = null, int $second = null, int $month = null, int $day = null, int $year = null) : int
{
    \error_clear_last();
    if ($year !== null) {
        $safeResult = \gmmktime($hour, $minute, $second, $month, $day, $year);
    } elseif ($day !== null) {
        $safeResult = \gmmktime($hour, $minute, $second, $month, $day);
    } elseif ($month !== null) {
        $safeResult = \gmmktime($hour, $minute, $second, $month);
    } elseif ($second !== null) {
        $safeResult = \gmmktime($hour, $minute, $second);
    } elseif ($minute !== null) {
        $safeResult = \gmmktime($hour, $minute);
    } else {
        $safeResult = \gmmktime($hour);
    }
    if ($safeResult === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $safeResult;
}
function gmstrftime(string $format, int $timestamp = null) : string
{
    \error_clear_last();
    if ($timestamp !== null) {
        $safeResult = \gmstrftime($format, $timestamp);
    } else {
        $safeResult = \gmstrftime($format);
    }
    if ($safeResult === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $safeResult;
}
function idate(string $format, int $timestamp = null) : int
{
    \error_clear_last();
    if ($timestamp !== null) {
        $safeResult = \idate($format, $timestamp);
    } else {
        $safeResult = \idate($format);
    }
    if ($safeResult === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $safeResult;
}
function strftime(string $format, int $timestamp = null) : string
{
    \error_clear_last();
    if ($timestamp !== null) {
        $safeResult = \strftime($format, $timestamp);
    } else {
        $safeResult = \strftime($format);
    }
    if ($safeResult === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $safeResult;
}
function strptime(string $timestamp, string $format) : array
{
    \error_clear_last();
    $safeResult = \strptime($timestamp, $format);
    if ($safeResult === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $safeResult;
}
function strtotime(string $datetime, int $baseTimestamp = null) : int
{
    \error_clear_last();
    if ($baseTimestamp !== null) {
        $safeResult = \strtotime($datetime, $baseTimestamp);
    } else {
        $safeResult = \strtotime($datetime);
    }
    if ($safeResult === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $safeResult;
}
function timezone_name_from_abbr(string $abbr, int $utcOffset = -1, int $isDST = -1) : string
{
    \error_clear_last();
    $safeResult = \timezone_name_from_abbr($abbr, $utcOffset, $isDST);
    if ($safeResult === \false) {
        throw DatetimeException::createFromPhpError();
    }
    return $safeResult;
}
