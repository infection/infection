<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\IbmDb2Exception;
function db2_autocommit($connection, int $value = null)
{
    \error_clear_last();
    if ($value !== null) {
        $safeResult = \db2_autocommit($connection, $value);
    } else {
        $safeResult = \db2_autocommit($connection);
    }
    if ($safeResult === \false) {
        throw IbmDb2Exception::createFromPhpError();
    }
    return $safeResult;
}
function db2_bind_param($stmt, int $parameter_number, string $variable_name, int $parameter_type = null, int $data_type = 0, int $precision = -1, int $scale = 0) : void
{
    \error_clear_last();
    if ($scale !== 0) {
        $safeResult = \db2_bind_param($stmt, $parameter_number, $variable_name, $parameter_type, $data_type, $precision, $scale);
    } elseif ($precision !== -1) {
        $safeResult = \db2_bind_param($stmt, $parameter_number, $variable_name, $parameter_type, $data_type, $precision);
    } elseif ($data_type !== 0) {
        $safeResult = \db2_bind_param($stmt, $parameter_number, $variable_name, $parameter_type, $data_type);
    } elseif ($parameter_type !== null) {
        $safeResult = \db2_bind_param($stmt, $parameter_number, $variable_name, $parameter_type);
    } else {
        $safeResult = \db2_bind_param($stmt, $parameter_number, $variable_name);
    }
    if ($safeResult === \false) {
        throw IbmDb2Exception::createFromPhpError();
    }
}
function db2_client_info($connection) : object
{
    \error_clear_last();
    $safeResult = \db2_client_info($connection);
    if ($safeResult === \false) {
        throw IbmDb2Exception::createFromPhpError();
    }
    return $safeResult;
}
function db2_close($connection) : void
{
    \error_clear_last();
    $safeResult = \db2_close($connection);
    if ($safeResult === \false) {
        throw IbmDb2Exception::createFromPhpError();
    }
}
function db2_commit($connection) : void
{
    \error_clear_last();
    $safeResult = \db2_commit($connection);
    if ($safeResult === \false) {
        throw IbmDb2Exception::createFromPhpError();
    }
}
function db2_execute($stmt, array $parameters = null) : void
{
    \error_clear_last();
    if ($parameters !== null) {
        $safeResult = \db2_execute($stmt, $parameters);
    } else {
        $safeResult = \db2_execute($stmt);
    }
    if ($safeResult === \false) {
        throw IbmDb2Exception::createFromPhpError();
    }
}
function db2_free_result($stmt) : void
{
    \error_clear_last();
    $safeResult = \db2_free_result($stmt);
    if ($safeResult === \false) {
        throw IbmDb2Exception::createFromPhpError();
    }
}
function db2_free_stmt($stmt) : void
{
    \error_clear_last();
    $safeResult = \db2_free_stmt($stmt);
    if ($safeResult === \false) {
        throw IbmDb2Exception::createFromPhpError();
    }
}
function db2_get_option($resource, string $option) : string
{
    \error_clear_last();
    $safeResult = \db2_get_option($resource, $option);
    if ($safeResult === \false) {
        throw IbmDb2Exception::createFromPhpError();
    }
    return $safeResult;
}
function db2_pclose($resource) : void
{
    \error_clear_last();
    $safeResult = \_HumbugBoxb47773b41c19\db2_pclose($resource);
    if ($safeResult === \false) {
        throw IbmDb2Exception::createFromPhpError();
    }
}
function db2_rollback($connection) : void
{
    \error_clear_last();
    $safeResult = \db2_rollback($connection);
    if ($safeResult === \false) {
        throw IbmDb2Exception::createFromPhpError();
    }
}
function db2_server_info($connection) : object
{
    \error_clear_last();
    $safeResult = \db2_server_info($connection);
    if ($safeResult === \false) {
        throw IbmDb2Exception::createFromPhpError();
    }
    return $safeResult;
}
function db2_set_option($resource, array $options, int $type) : void
{
    \error_clear_last();
    $safeResult = \db2_set_option($resource, $options, $type);
    if ($safeResult === \false) {
        throw IbmDb2Exception::createFromPhpError();
    }
}
