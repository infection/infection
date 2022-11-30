<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\SplException;
function class_implements($object_or_class, bool $autoload = \true) : array
{
    \error_clear_last();
    $result = \class_implements($object_or_class, $autoload);
    if ($result === \false) {
        throw SplException::createFromPhpError();
    }
    return $result;
}
function class_parents($object_or_class, bool $autoload = \true) : array
{
    \error_clear_last();
    $result = \class_parents($object_or_class, $autoload);
    if ($result === \false) {
        throw SplException::createFromPhpError();
    }
    return $result;
}
function class_uses($object_or_class, bool $autoload = \true) : array
{
    \error_clear_last();
    $result = \class_uses($object_or_class, $autoload);
    if ($result === \false) {
        throw SplException::createFromPhpError();
    }
    return $result;
}
function spl_autoload_register(callable $callback = null, bool $throw = \true, bool $prepend = \false) : void
{
    \error_clear_last();
    if ($prepend !== \false) {
        $result = \spl_autoload_register($callback, $throw, $prepend);
    } elseif ($throw !== \true) {
        $result = \spl_autoload_register($callback, $throw);
    } elseif ($callback !== null) {
        $result = \spl_autoload_register($callback);
    } else {
        $result = \spl_autoload_register();
    }
    if ($result === \false) {
        throw SplException::createFromPhpError();
    }
}
function spl_autoload_unregister($callback) : void
{
    \error_clear_last();
    $result = \spl_autoload_unregister($callback);
    if ($result === \false) {
        throw SplException::createFromPhpError();
    }
}
