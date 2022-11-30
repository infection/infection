<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\SessionException;
function session_abort() : void
{
    \error_clear_last();
    $safeResult = \session_abort();
    if ($safeResult === \false) {
        throw SessionException::createFromPhpError();
    }
}
function session_create_id(string $prefix = "") : string
{
    \error_clear_last();
    $safeResult = \session_create_id($prefix);
    if ($safeResult === \false) {
        throw SessionException::createFromPhpError();
    }
    return $safeResult;
}
function session_decode(string $data) : void
{
    \error_clear_last();
    $safeResult = \session_decode($data);
    if ($safeResult === \false) {
        throw SessionException::createFromPhpError();
    }
}
function session_destroy() : void
{
    \error_clear_last();
    $safeResult = \session_destroy();
    if ($safeResult === \false) {
        throw SessionException::createFromPhpError();
    }
}
function session_encode() : string
{
    \error_clear_last();
    $safeResult = \session_encode();
    if ($safeResult === \false) {
        throw SessionException::createFromPhpError();
    }
    return $safeResult;
}
function session_id(string $id = null) : string
{
    \error_clear_last();
    if ($id !== null) {
        $safeResult = \session_id($id);
    } else {
        $safeResult = \session_id();
    }
    if ($safeResult === \false) {
        throw SessionException::createFromPhpError();
    }
    return $safeResult;
}
function session_module_name(string $module = null) : string
{
    \error_clear_last();
    if ($module !== null) {
        $safeResult = \session_module_name($module);
    } else {
        $safeResult = \session_module_name();
    }
    if ($safeResult === \false) {
        throw SessionException::createFromPhpError();
    }
    return $safeResult;
}
function session_name(string $name = null) : string
{
    \error_clear_last();
    if ($name !== null) {
        $safeResult = \session_name($name);
    } else {
        $safeResult = \session_name();
    }
    if ($safeResult === \false) {
        throw SessionException::createFromPhpError();
    }
    return $safeResult;
}
function session_regenerate_id(bool $delete_old_session = \false) : void
{
    \error_clear_last();
    $safeResult = \session_regenerate_id($delete_old_session);
    if ($safeResult === \false) {
        throw SessionException::createFromPhpError();
    }
}
function session_reset() : void
{
    \error_clear_last();
    $safeResult = \session_reset();
    if ($safeResult === \false) {
        throw SessionException::createFromPhpError();
    }
}
function session_save_path(string $path = null) : string
{
    \error_clear_last();
    if ($path !== null) {
        $safeResult = \session_save_path($path);
    } else {
        $safeResult = \session_save_path();
    }
    if ($safeResult === \false) {
        throw SessionException::createFromPhpError();
    }
    return $safeResult;
}
function session_unset() : void
{
    \error_clear_last();
    $safeResult = \session_unset();
    if ($safeResult === \false) {
        throw SessionException::createFromPhpError();
    }
}
function session_write_close() : void
{
    \error_clear_last();
    $safeResult = \session_write_close();
    if ($safeResult === \false) {
        throw SessionException::createFromPhpError();
    }
}
