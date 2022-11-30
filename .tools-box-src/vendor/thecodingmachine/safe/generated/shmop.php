<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\ShmopException;
function shmop_delete($shmop) : void
{
    \error_clear_last();
    $safeResult = \shmop_delete($shmop);
    if ($safeResult === \false) {
        throw ShmopException::createFromPhpError();
    }
}
function shmop_read($shmop, int $offset, int $size) : string
{
    \error_clear_last();
    $safeResult = \shmop_read($shmop, $offset, $size);
    if ($safeResult === \false) {
        throw ShmopException::createFromPhpError();
    }
    return $safeResult;
}
