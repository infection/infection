<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\DirException;
function chdir(string $directory) : void
{
    \error_clear_last();
    $safeResult = \chdir($directory);
    if ($safeResult === \false) {
        throw DirException::createFromPhpError();
    }
}
function chroot(string $directory) : void
{
    \error_clear_last();
    $safeResult = \chroot($directory);
    if ($safeResult === \false) {
        throw DirException::createFromPhpError();
    }
}
function getcwd() : string
{
    \error_clear_last();
    $safeResult = \getcwd();
    if ($safeResult === \false) {
        throw DirException::createFromPhpError();
    }
    return $safeResult;
}
function opendir(string $directory, $context = null)
{
    \error_clear_last();
    if ($context !== null) {
        $safeResult = \opendir($directory, $context);
    } else {
        $safeResult = \opendir($directory);
    }
    if ($safeResult === \false) {
        throw DirException::createFromPhpError();
    }
    return $safeResult;
}
function scandir(string $directory, int $sorting_order = \SCANDIR_SORT_ASCENDING, $context = null) : array
{
    \error_clear_last();
    if ($context !== null) {
        $safeResult = \scandir($directory, $sorting_order, $context);
    } else {
        $safeResult = \scandir($directory, $sorting_order);
    }
    if ($safeResult === \false) {
        throw DirException::createFromPhpError();
    }
    return $safeResult;
}
