<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\ArrayException;
function array_combine(array $keys, array $values) : array
{
    \error_clear_last();
    $result = \array_combine($keys, $values);
    if ($result === \false) {
        throw ArrayException::createFromPhpError();
    }
    return $result;
}
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
function array_walk_recursive(&$array, callable $callback, $arg = null) : void
{
    \error_clear_last();
    if ($arg !== null) {
        $result = \array_walk_recursive($array, $callback, $arg);
    } else {
        $result = \array_walk_recursive($array, $callback);
    }
    if ($result === \false) {
        throw ArrayException::createFromPhpError();
    }
}
function shuffle(array &$array) : void
{
    \error_clear_last();
    $result = \shuffle($array);
    if ($result === \false) {
        throw ArrayException::createFromPhpError();
    }
}
