<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\SessionException;
function session_abort() : void
{
    \error_clear_last();
    $result = \session_abort();
    if ($result === \false) {
        throw SessionException::createFromPhpError();
    }
}
function session_create_id(string $prefix = "") : string
{
    \error_clear_last();
    $result = \session_create_id($prefix);
    if ($result === \false) {
        throw SessionException::createFromPhpError();
    }
    return $result;
}
function session_decode(string $data) : void
{
    \error_clear_last();
    $result = \session_decode($data);
    if ($result === \false) {
        throw SessionException::createFromPhpError();
    }
}
function session_destroy() : void
{
    \error_clear_last();
    $result = \session_destroy();
    if ($result === \false) {
        throw SessionException::createFromPhpError();
    }
}
function session_encode() : string
{
    \error_clear_last();
    $result = \session_encode();
    if ($result === \false) {
        throw SessionException::createFromPhpError();
    }
    return $result;
}
function session_id(string $id = null) : string
{
    \error_clear_last();
    if ($id !== null) {
        $result = \session_id($id);
    } else {
        $result = \session_id();
    }
    if ($result === \false) {
        throw SessionException::createFromPhpError();
    }
    return $result;
}
function session_module_name(string $module = null) : string
{
    \error_clear_last();
    if ($module !== null) {
        $result = \session_module_name($module);
    } else {
        $result = \session_module_name();
    }
    if ($result === \false) {
        throw SessionException::createFromPhpError();
    }
    return $result;
}
function session_name(string $name = null) : string
{
    \error_clear_last();
    if ($name !== null) {
        $result = \session_name($name);
    } else {
        $result = \session_name();
    }
    if ($result === \false) {
        throw SessionException::createFromPhpError();
    }
    return $result;
}
function session_regenerate_id(bool $delete_old_session = \false) : void
{
    \error_clear_last();
    $result = \session_regenerate_id($delete_old_session);
    if ($result === \false) {
        throw SessionException::createFromPhpError();
    }
}
function session_reset() : void
{
    \error_clear_last();
    $result = \session_reset();
    if ($result === \false) {
        throw SessionException::createFromPhpError();
    }
}
function session_save_path(string $path = null) : string
{
    \error_clear_last();
    if ($path !== null) {
        $result = \session_save_path($path);
    } else {
        $result = \session_save_path();
    }
    if ($result === \false) {
        throw SessionException::createFromPhpError();
    }
    return $result;
}
function session_unset() : void
{
    \error_clear_last();
    $result = \session_unset();
    if ($result === \false) {
        throw SessionException::createFromPhpError();
    }
}
function session_write_close() : void
{
    \error_clear_last();
    $result = \session_write_close();
    if ($result === \false) {
        throw SessionException::createFromPhpError();
    }
}
