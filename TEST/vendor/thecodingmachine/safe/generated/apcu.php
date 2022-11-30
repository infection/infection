<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\ApcuException;
function apcu_cache_info(bool $limited = \false) : array
{
    \error_clear_last();
    $result = \apcu_cache_info($limited);
    if ($result === \false) {
        throw ApcuException::createFromPhpError();
    }
    return $result;
}
function apcu_cas(string $key, int $old, int $new) : void
{
    \error_clear_last();
    $result = \apcu_cas($key, $old, $new);
    if ($result === \false) {
        throw ApcuException::createFromPhpError();
    }
}
function apcu_dec(string $key, int $step = 1, ?bool &$success = null, int $ttl = 0) : int
{
    \error_clear_last();
    $result = \apcu_dec($key, $step, $success, $ttl);
    if ($result === \false) {
        throw ApcuException::createFromPhpError();
    }
    return $result;
}
function apcu_inc(string $key, int $step = 1, ?bool &$success = null, int $ttl = 0) : int
{
    \error_clear_last();
    $result = \apcu_inc($key, $step, $success, $ttl);
    if ($result === \false) {
        throw ApcuException::createFromPhpError();
    }
    return $result;
}
function apcu_sma_info(bool $limited = \false) : array
{
    \error_clear_last();
    $result = \apcu_sma_info($limited);
    if ($result === \false) {
        throw ApcuException::createFromPhpError();
    }
    return $result;
}
