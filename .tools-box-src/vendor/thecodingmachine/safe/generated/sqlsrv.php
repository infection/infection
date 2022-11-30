<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\SqlsrvException;
function sqlsrv_begin_transaction($conn) : void
{
    \error_clear_last();
    $safeResult = \sqlsrv_begin_transaction($conn);
    if ($safeResult === \false) {
        throw SqlsrvException::createFromPhpError();
    }
}
function sqlsrv_cancel($stmt) : void
{
    \error_clear_last();
    $safeResult = \sqlsrv_cancel($stmt);
    if ($safeResult === \false) {
        throw SqlsrvException::createFromPhpError();
    }
}
function sqlsrv_client_info($conn) : array
{
    \error_clear_last();
    $safeResult = \sqlsrv_client_info($conn);
    if ($safeResult === \false) {
        throw SqlsrvException::createFromPhpError();
    }
    return $safeResult;
}
function sqlsrv_close($conn) : void
{
    \error_clear_last();
    $safeResult = \sqlsrv_close($conn);
    if ($safeResult === \false) {
        throw SqlsrvException::createFromPhpError();
    }
}
function sqlsrv_commit($conn) : void
{
    \error_clear_last();
    $safeResult = \sqlsrv_commit($conn);
    if ($safeResult === \false) {
        throw SqlsrvException::createFromPhpError();
    }
}
function sqlsrv_configure(string $setting, $value) : void
{
    \error_clear_last();
    $safeResult = \sqlsrv_configure($setting, $value);
    if ($safeResult === \false) {
        throw SqlsrvException::createFromPhpError();
    }
}
function sqlsrv_execute($stmt) : void
{
    \error_clear_last();
    $safeResult = \sqlsrv_execute($stmt);
    if ($safeResult === \false) {
        throw SqlsrvException::createFromPhpError();
    }
}
function sqlsrv_free_stmt($stmt) : void
{
    \error_clear_last();
    $safeResult = \sqlsrv_free_stmt($stmt);
    if ($safeResult === \false) {
        throw SqlsrvException::createFromPhpError();
    }
}
function sqlsrv_get_field($stmt, int $fieldIndex, int $getAsType = null)
{
    \error_clear_last();
    if ($getAsType !== null) {
        $safeResult = \sqlsrv_get_field($stmt, $fieldIndex, $getAsType);
    } else {
        $safeResult = \sqlsrv_get_field($stmt, $fieldIndex);
    }
    if ($safeResult === \false) {
        throw SqlsrvException::createFromPhpError();
    }
    return $safeResult;
}
function sqlsrv_next_result($stmt) : ?bool
{
    \error_clear_last();
    $safeResult = \sqlsrv_next_result($stmt);
    if ($safeResult === \false) {
        throw SqlsrvException::createFromPhpError();
    }
    return $safeResult;
}
function sqlsrv_num_fields($stmt) : int
{
    \error_clear_last();
    $safeResult = \sqlsrv_num_fields($stmt);
    if ($safeResult === \false) {
        throw SqlsrvException::createFromPhpError();
    }
    return $safeResult;
}
function sqlsrv_num_rows($stmt) : int
{
    \error_clear_last();
    $safeResult = \sqlsrv_num_rows($stmt);
    if ($safeResult === \false) {
        throw SqlsrvException::createFromPhpError();
    }
    return $safeResult;
}
function sqlsrv_prepare($conn, string $sql, array $params = null, array $options = null)
{
    \error_clear_last();
    if ($options !== null) {
        $safeResult = \sqlsrv_prepare($conn, $sql, $params, $options);
    } elseif ($params !== null) {
        $safeResult = \sqlsrv_prepare($conn, $sql, $params);
    } else {
        $safeResult = \sqlsrv_prepare($conn, $sql);
    }
    if ($safeResult === \false) {
        throw SqlsrvException::createFromPhpError();
    }
    return $safeResult;
}
function sqlsrv_query($conn, string $sql, array $params = null, array $options = null)
{
    \error_clear_last();
    if ($options !== null) {
        $safeResult = \sqlsrv_query($conn, $sql, $params, $options);
    } elseif ($params !== null) {
        $safeResult = \sqlsrv_query($conn, $sql, $params);
    } else {
        $safeResult = \sqlsrv_query($conn, $sql);
    }
    if ($safeResult === \false) {
        throw SqlsrvException::createFromPhpError();
    }
    return $safeResult;
}
function sqlsrv_rollback($conn) : void
{
    \error_clear_last();
    $safeResult = \sqlsrv_rollback($conn);
    if ($safeResult === \false) {
        throw SqlsrvException::createFromPhpError();
    }
}
