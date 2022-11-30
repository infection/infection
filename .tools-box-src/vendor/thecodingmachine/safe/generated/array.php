<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\ArrayException;
function array_walk_recursive(&$array, callable $callback, $arg = null) : void
{
    \error_clear_last();
    if ($arg !== null) {
        $safeResult = \array_walk_recursive($array, $callback, $arg);
    } else {
        $safeResult = \array_walk_recursive($array, $callback);
    }
    if ($safeResult === \false) {
        throw ArrayException::createFromPhpError();
    }
}
function shuffle(array &$array) : void
{
    \error_clear_last();
    $safeResult = \shuffle($array);
    if ($safeResult === \false) {
        throw ArrayException::createFromPhpError();
    }
}
