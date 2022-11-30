<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\OpcacheException;
function opcache_compile_file(string $filename) : void
{
    \error_clear_last();
    $result = \opcache_compile_file($filename);
    if ($result === \false) {
        throw OpcacheException::createFromPhpError();
    }
}
function opcache_get_status(bool $include_scripts = \true) : array
{
    \error_clear_last();
    $result = \opcache_get_status($include_scripts);
    if ($result === \false) {
        throw OpcacheException::createFromPhpError();
    }
    return $result;
}
