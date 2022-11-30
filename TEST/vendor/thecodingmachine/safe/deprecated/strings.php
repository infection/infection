<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\StringsException;
function sprintf(string $format, ...$params) : string
{
    \error_clear_last();
    if ($params !== []) {
        $result = \sprintf($format, ...$params);
    } else {
        $result = \sprintf($format);
    }
    if ($result === \false) {
        throw StringsException::createFromPhpError();
    }
    return $result;
}
function substr(string $string, int $start, int $length = null) : string
{
    \error_clear_last();
    if ($length !== null) {
        $result = \substr($string, $start, $length);
    } else {
        $result = \substr($string, $start);
    }
    if ($result === \false) {
        throw StringsException::createFromPhpError();
    }
    return $result;
}
function vsprintf(string $format, array $args) : string
{
    \error_clear_last();
    $result = \vsprintf($format, $args);
    if ($result === \false) {
        throw StringsException::createFromPhpError();
    }
    return $result;
}
