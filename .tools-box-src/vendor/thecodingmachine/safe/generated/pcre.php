<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\PcreException;
function preg_grep(string $pattern, array $array, int $flags = 0) : array
{
    \error_clear_last();
    $safeResult = \preg_grep($pattern, $array, $flags);
    if ($safeResult === \false) {
        throw PcreException::createFromPhpError();
    }
    return $safeResult;
}
function preg_match_all(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0) : ?int
{
    \error_clear_last();
    $safeResult = \preg_match_all($pattern, $subject, $matches, $flags, $offset);
    if ($safeResult === \false) {
        throw PcreException::createFromPhpError();
    }
    return $safeResult;
}
function preg_match(string $pattern, string $subject, ?iterable &$matches = null, int $flags = 0, int $offset = 0) : int
{
    \error_clear_last();
    $safeResult = \preg_match($pattern, $subject, $matches, $flags, $offset);
    if ($safeResult === \false) {
        throw PcreException::createFromPhpError();
    }
    return $safeResult;
}
function preg_split(string $pattern, string $subject, ?int $limit = -1, int $flags = 0) : array
{
    \error_clear_last();
    $safeResult = \preg_split($pattern, $subject, $limit, $flags);
    if ($safeResult === \false) {
        throw PcreException::createFromPhpError();
    }
    return $safeResult;
}
