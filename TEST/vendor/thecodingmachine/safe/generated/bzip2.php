<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\Bzip2Exception;
function bzclose($bz) : void
{
    \error_clear_last();
    $result = \bzclose($bz);
    if ($result === \false) {
        throw Bzip2Exception::createFromPhpError();
    }
}
function bzflush($bz) : void
{
    \error_clear_last();
    $result = \bzflush($bz);
    if ($result === \false) {
        throw Bzip2Exception::createFromPhpError();
    }
}
function bzread($bz, int $length = 1024) : string
{
    \error_clear_last();
    $result = \bzread($bz, $length);
    if ($result === \false) {
        throw Bzip2Exception::createFromPhpError();
    }
    return $result;
}
function bzwrite($bz, string $data, int $length = null) : int
{
    \error_clear_last();
    if ($length !== null) {
        $result = \bzwrite($bz, $data, $length);
    } else {
        $result = \bzwrite($bz, $data);
    }
    if ($result === \false) {
        throw Bzip2Exception::createFromPhpError();
    }
    return $result;
}
