<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\Bzip2Exception;
function bzclose($bz) : void
{
    \error_clear_last();
    $safeResult = \bzclose($bz);
    if ($safeResult === \false) {
        throw Bzip2Exception::createFromPhpError();
    }
}
function bzflush($bz) : void
{
    \error_clear_last();
    $safeResult = \bzflush($bz);
    if ($safeResult === \false) {
        throw Bzip2Exception::createFromPhpError();
    }
}
function bzread($bz, int $length = 1024) : string
{
    \error_clear_last();
    $safeResult = \bzread($bz, $length);
    if ($safeResult === \false) {
        throw Bzip2Exception::createFromPhpError();
    }
    return $safeResult;
}
function bzwrite($bz, string $data, int $length = null) : int
{
    \error_clear_last();
    if ($length !== null) {
        $safeResult = \bzwrite($bz, $data, $length);
    } else {
        $safeResult = \bzwrite($bz, $data);
    }
    if ($safeResult === \false) {
        throw Bzip2Exception::createFromPhpError();
    }
    return $safeResult;
}
