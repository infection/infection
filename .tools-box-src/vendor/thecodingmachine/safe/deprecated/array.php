<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\ArrayException;
function array_replace_recursive(array $array, array ...$replacements) : array
{
    \error_clear_last();
    if ($replacements !== []) {
        $result = \array_replace_recursive($array, ...$replacements);
    } else {
        $result = \array_replace_recursive($array);
    }
    if ($result === null) {
        throw ArrayException::createFromPhpError();
    }
    return $result;
}
function array_replace(array $array, array ...$replacements) : array
{
    \error_clear_last();
    if ($replacements !== []) {
        $result = \array_replace($array, ...$replacements);
    } else {
        $result = \array_replace($array);
    }
    if ($result === null) {
        throw ArrayException::createFromPhpError();
    }
    return $result;
}
function array_flip(array $array) : array
{
    \error_clear_last();
    $result = \array_flip($array);
    if ($result === null) {
        throw ArrayException::createFromPhpError();
    }
    return $result;
}
function arsort(array &$array, int $sort_flags = \SORT_REGULAR) : void
{
    \error_clear_last();
    $result = \arsort($array, $sort_flags);
    if ($result === \false) {
        throw ArrayException::createFromPhpError();
    }
}
function asort(array &$array, int $sort_flags = \SORT_REGULAR) : void
{
    \error_clear_last();
    $result = \asort($array, $sort_flags);
    if ($result === \false) {
        throw ArrayException::createFromPhpError();
    }
}
function krsort(array &$array, int $sort_flags = \SORT_REGULAR) : void
{
    \error_clear_last();
    $result = \krsort($array, $sort_flags);
    if ($result === \false) {
        throw ArrayException::createFromPhpError();
    }
}
function ksort(array &$array, int $sort_flags = \SORT_REGULAR) : void
{
    \error_clear_last();
    $result = \ksort($array, $sort_flags);
    if ($result === \false) {
        throw ArrayException::createFromPhpError();
    }
}
function sort(array &$array, int $sort_flags = \SORT_REGULAR) : void
{
    \error_clear_last();
    $result = \sort($array, $sort_flags);
    if ($result === \false) {
        throw ArrayException::createFromPhpError();
    }
}
function usort(array &$array, callable $value_compare_func) : void
{
    \error_clear_last();
    $result = \usort($array, $value_compare_func);
    if ($result === \false) {
        throw ArrayException::createFromPhpError();
    }
}
function array_combine(array $keys, array $values) : array
{
    \error_clear_last();
    $result = \array_combine($keys, $values);
    if ($result === \false) {
        throw ArrayException::createFromPhpError();
    }
    return $result;
}
