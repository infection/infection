<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\ApcuException;
function apcu_cache_info(bool $limited = \false) : array
{
    \error_clear_last();
    $safeResult = \apcu_cache_info($limited);
    if ($safeResult === \false) {
        throw ApcuException::createFromPhpError();
    }
    return $safeResult;
}
function apcu_cas(string $key, int $old, int $new) : void
{
    \error_clear_last();
    $safeResult = \apcu_cas($key, $old, $new);
    if ($safeResult === \false) {
        throw ApcuException::createFromPhpError();
    }
}
function apcu_dec(string $key, int $step = 1, ?bool &$success = null, int $ttl = 0) : int
{
    \error_clear_last();
    $safeResult = \apcu_dec($key, $step, $success, $ttl);
    if ($safeResult === \false) {
        throw ApcuException::createFromPhpError();
    }
    return $safeResult;
}
function apcu_inc(string $key, int $step = 1, ?bool &$success = null, int $ttl = 0) : int
{
    \error_clear_last();
    $safeResult = \apcu_inc($key, $step, $success, $ttl);
    if ($safeResult === \false) {
        throw ApcuException::createFromPhpError();
    }
    return $safeResult;
}
function apcu_sma_info(bool $limited = \false) : array
{
    \error_clear_last();
    $safeResult = \apcu_sma_info($limited);
    if ($safeResult === \false) {
        throw ApcuException::createFromPhpError();
    }
    return $safeResult;
}
