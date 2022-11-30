<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\IbmDb2Exception;
function db2_autocommit($connection, int $value = null)
{
    \error_clear_last();
    if ($value !== null) {
        $result = \db2_autocommit($connection, $value);
    } else {
        $result = \db2_autocommit($connection);
    }
    if ($result === \false) {
        throw IbmDb2Exception::createFromPhpError();
    }
    return $result;
}
function db2_bind_param($stmt, int $parameter_number, string $variable_name, int $parameter_type = null, int $data_type = 0, int $precision = -1, int $scale = 0) : void
{
    \error_clear_last();
    if ($scale !== 0) {
        $result = \db2_bind_param($stmt, $parameter_number, $variable_name, $parameter_type, $data_type, $precision, $scale);
    } elseif ($precision !== -1) {
        $result = \db2_bind_param($stmt, $parameter_number, $variable_name, $parameter_type, $data_type, $precision);
    } elseif ($data_type !== 0) {
        $result = \db2_bind_param($stmt, $parameter_number, $variable_name, $parameter_type, $data_type);
    } elseif ($parameter_type !== null) {
        $result = \db2_bind_param($stmt, $parameter_number, $variable_name, $parameter_type);
    } else {
        $result = \db2_bind_param($stmt, $parameter_number, $variable_name);
    }
    if ($result === \false) {
        throw IbmDb2Exception::createFromPhpError();
    }
}
function db2_client_info($connection) : object
{
    \error_clear_last();
    $result = \db2_client_info($connection);
    if ($result === \false) {
        throw IbmDb2Exception::createFromPhpError();
    }
    return $result;
}
function db2_close($connection) : void
{
    \error_clear_last();
    $result = \db2_close($connection);
    if ($result === \false) {
        throw IbmDb2Exception::createFromPhpError();
    }
}
function db2_commit($connection) : void
{
    \error_clear_last();
    $result = \db2_commit($connection);
    if ($result === \false) {
        throw IbmDb2Exception::createFromPhpError();
    }
}
function db2_execute($stmt, array $parameters = null) : void
{
    \error_clear_last();
    if ($parameters !== null) {
        $result = \db2_execute($stmt, $parameters);
    } else {
        $result = \db2_execute($stmt);
    }
    if ($result === \false) {
        throw IbmDb2Exception::createFromPhpError();
    }
}
function db2_free_result($stmt) : void
{
    \error_clear_last();
    $result = \db2_free_result($stmt);
    if ($result === \false) {
        throw IbmDb2Exception::createFromPhpError();
    }
}
function db2_free_stmt($stmt) : void
{
    \error_clear_last();
    $result = \db2_free_stmt($stmt);
    if ($result === \false) {
        throw IbmDb2Exception::createFromPhpError();
    }
}
function db2_get_option($resource, string $option) : string
{
    \error_clear_last();
    $result = \db2_get_option($resource, $option);
    if ($result === \false) {
        throw IbmDb2Exception::createFromPhpError();
    }
    return $result;
}
function db2_pclose($resource) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\db2_pclose($resource);
    if ($result === \false) {
        throw IbmDb2Exception::createFromPhpError();
    }
}
function db2_rollback($connection) : void
{
    \error_clear_last();
    $result = \db2_rollback($connection);
    if ($result === \false) {
        throw IbmDb2Exception::createFromPhpError();
    }
}
function db2_server_info($connection) : object
{
    \error_clear_last();
    $result = \db2_server_info($connection);
    if ($result === \false) {
        throw IbmDb2Exception::createFromPhpError();
    }
    return $result;
}
function db2_set_option($resource, array $options, int $type) : void
{
    \error_clear_last();
    $result = \db2_set_option($resource, $options, $type);
    if ($result === \false) {
        throw IbmDb2Exception::createFromPhpError();
    }
}
