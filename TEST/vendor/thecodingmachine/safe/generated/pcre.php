<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\PcreException;
function preg_grep(string $pattern, array $array, int $flags = 0) : array
{
    \error_clear_last();
    $result = \preg_grep($pattern, $array, $flags);
    if ($result === \false) {
        throw PcreException::createFromPhpError();
    }
    return $result;
}
function preg_match_all(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0) : int
{
    \error_clear_last();
    $result = \preg_match_all($pattern, $subject, $matches, $flags, $offset);
    if ($result === \false) {
        throw PcreException::createFromPhpError();
    }
    return $result;
}
function preg_match(string $pattern, string $subject, ?iterable &$matches = null, int $flags = 0, int $offset = 0) : int
{
    \error_clear_last();
    $result = \preg_match($pattern, $subject, $matches, $flags, $offset);
    if ($result === \false) {
        throw PcreException::createFromPhpError();
    }
    return $result;
}
function preg_split(string $pattern, string $subject, ?int $limit = -1, int $flags = 0) : array
{
    \error_clear_last();
    $result = \preg_split($pattern, $subject, $limit, $flags);
    if ($result === \false) {
        throw PcreException::createFromPhpError();
    }
    return $result;
}
