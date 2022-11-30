<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\DirException;
function chdir(string $directory) : void
{
    \error_clear_last();
    $result = \chdir($directory);
    if ($result === \false) {
        throw DirException::createFromPhpError();
    }
}
function chroot(string $directory) : void
{
    \error_clear_last();
    $result = \chroot($directory);
    if ($result === \false) {
        throw DirException::createFromPhpError();
    }
}
function getcwd() : string
{
    \error_clear_last();
    $result = \getcwd();
    if ($result === \false) {
        throw DirException::createFromPhpError();
    }
    return $result;
}
function opendir(string $directory, $context = null)
{
    \error_clear_last();
    if ($context !== null) {
        $result = \opendir($directory, $context);
    } else {
        $result = \opendir($directory);
    }
    if ($result === \false) {
        throw DirException::createFromPhpError();
    }
    return $result;
}
function scandir(string $directory, int $sorting_order = \SCANDIR_SORT_ASCENDING, $context = null) : array
{
    \error_clear_last();
    if ($context !== null) {
        $result = \scandir($directory, $sorting_order, $context);
    } else {
        $result = \scandir($directory, $sorting_order);
    }
    if ($result === \false) {
        throw DirException::createFromPhpError();
    }
    return $result;
}
