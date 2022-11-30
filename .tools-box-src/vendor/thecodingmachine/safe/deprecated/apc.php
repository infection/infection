<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\ApcException;
function apc_cache_info(string $cache_type = '', bool $limited = \false) : array
{
    \error_clear_last();
    $result = \apc_cache_info($cache_type, $limited);
    if ($result === \false) {
        throw ApcException::createFromPhpError();
    }
    return $result;
}
function apc_cas(string $key, int $old, int $new) : void
{
    \error_clear_last();
    $result = \apc_cas($key, $old, $new);
    if ($result === \false) {
        throw ApcException::createFromPhpError();
    }
}
function apc_compile_file(string $filename, bool $atomic = \true)
{
    \error_clear_last();
    $result = \apc_compile_file($filename, $atomic);
    if ($result === \false) {
        throw ApcException::createFromPhpError();
    }
    return $result;
}
function apc_dec(string $key, int $step = 1, ?bool &$success = null) : int
{
    \error_clear_last();
    $result = \apc_dec($key, $step, $success);
    if ($result === \false) {
        throw ApcException::createFromPhpError();
    }
    return $result;
}
function apc_define_constants(string $key, array $constants, bool $case_sensitive = \true) : void
{
    \error_clear_last();
    $result = \apc_define_constants($key, $constants, $case_sensitive);
    if ($result === \false) {
        throw ApcException::createFromPhpError();
    }
}
function apc_delete_file($keys)
{
    \error_clear_last();
    $result = \apc_delete_file($keys);
    if ($result === \false) {
        throw ApcException::createFromPhpError();
    }
    return $result;
}
function apc_delete($key) : void
{
    \error_clear_last();
    $result = \apc_delete($key);
    if ($result === \false) {
        throw ApcException::createFromPhpError();
    }
}
function apc_inc(string $key, int $step = 1, ?bool &$success = null) : int
{
    \error_clear_last();
    $result = \apc_inc($key, $step, $success);
    if ($result === \false) {
        throw ApcException::createFromPhpError();
    }
    return $result;
}
function apc_load_constants(string $key, bool $case_sensitive = \true) : void
{
    \error_clear_last();
    $result = \apc_load_constants($key, $case_sensitive);
    if ($result === \false) {
        throw ApcException::createFromPhpError();
    }
}
function apc_sma_info(bool $limited = \false) : array
{
    \error_clear_last();
    $result = \apc_sma_info($limited);
    if ($result === \false) {
        throw ApcException::createFromPhpError();
    }
    return $result;
}
