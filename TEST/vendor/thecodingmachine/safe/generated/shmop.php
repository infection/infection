<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\ShmopException;
function shmop_delete($shmop) : void
{
    \error_clear_last();
    $result = \shmop_delete($shmop);
    if ($result === \false) {
        throw ShmopException::createFromPhpError();
    }
}
function shmop_read($shmop, int $offset, int $size) : string
{
    \error_clear_last();
    $result = \shmop_read($shmop, $offset, $size);
    if ($result === \false) {
        throw ShmopException::createFromPhpError();
    }
    return $result;
}
