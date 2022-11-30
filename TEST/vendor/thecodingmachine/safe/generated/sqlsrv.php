<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\SqlsrvException;
function sqlsrv_begin_transaction($conn) : void
{
    \error_clear_last();
    $result = \sqlsrv_begin_transaction($conn);
    if ($result === \false) {
        throw SqlsrvException::createFromPhpError();
    }
}
function sqlsrv_cancel($stmt) : void
{
    \error_clear_last();
    $result = \sqlsrv_cancel($stmt);
    if ($result === \false) {
        throw SqlsrvException::createFromPhpError();
    }
}
function sqlsrv_client_info($conn) : array
{
    \error_clear_last();
    $result = \sqlsrv_client_info($conn);
    if ($result === \false) {
        throw SqlsrvException::createFromPhpError();
    }
    return $result;
}
function sqlsrv_close($conn) : void
{
    \error_clear_last();
    $result = \sqlsrv_close($conn);
    if ($result === \false) {
        throw SqlsrvException::createFromPhpError();
    }
}
function sqlsrv_commit($conn) : void
{
    \error_clear_last();
    $result = \sqlsrv_commit($conn);
    if ($result === \false) {
        throw SqlsrvException::createFromPhpError();
    }
}
function sqlsrv_configure(string $setting, $value) : void
{
    \error_clear_last();
    $result = \sqlsrv_configure($setting, $value);
    if ($result === \false) {
        throw SqlsrvException::createFromPhpError();
    }
}
function sqlsrv_execute($stmt) : void
{
    \error_clear_last();
    $result = \sqlsrv_execute($stmt);
    if ($result === \false) {
        throw SqlsrvException::createFromPhpError();
    }
}
function sqlsrv_free_stmt($stmt) : void
{
    \error_clear_last();
    $result = \sqlsrv_free_stmt($stmt);
    if ($result === \false) {
        throw SqlsrvException::createFromPhpError();
    }
}
function sqlsrv_get_field($stmt, int $fieldIndex, int $getAsType = null)
{
    \error_clear_last();
    if ($getAsType !== null) {
        $result = \sqlsrv_get_field($stmt, $fieldIndex, $getAsType);
    } else {
        $result = \sqlsrv_get_field($stmt, $fieldIndex);
    }
    if ($result === \false) {
        throw SqlsrvException::createFromPhpError();
    }
    return $result;
}
function sqlsrv_next_result($stmt) : ?bool
{
    \error_clear_last();
    $result = \sqlsrv_next_result($stmt);
    if ($result === \false) {
        throw SqlsrvException::createFromPhpError();
    }
    return $result;
}
function sqlsrv_num_fields($stmt) : int
{
    \error_clear_last();
    $result = \sqlsrv_num_fields($stmt);
    if ($result === \false) {
        throw SqlsrvException::createFromPhpError();
    }
    return $result;
}
function sqlsrv_num_rows($stmt) : int
{
    \error_clear_last();
    $result = \sqlsrv_num_rows($stmt);
    if ($result === \false) {
        throw SqlsrvException::createFromPhpError();
    }
    return $result;
}
function sqlsrv_prepare($conn, string $sql, array $params = null, array $options = null)
{
    \error_clear_last();
    if ($options !== null) {
        $result = \sqlsrv_prepare($conn, $sql, $params, $options);
    } elseif ($params !== null) {
        $result = \sqlsrv_prepare($conn, $sql, $params);
    } else {
        $result = \sqlsrv_prepare($conn, $sql);
    }
    if ($result === \false) {
        throw SqlsrvException::createFromPhpError();
    }
    return $result;
}
function sqlsrv_query($conn, string $sql, array $params = null, array $options = null)
{
    \error_clear_last();
    if ($options !== null) {
        $result = \sqlsrv_query($conn, $sql, $params, $options);
    } elseif ($params !== null) {
        $result = \sqlsrv_query($conn, $sql, $params);
    } else {
        $result = \sqlsrv_query($conn, $sql);
    }
    if ($result === \false) {
        throw SqlsrvException::createFromPhpError();
    }
    return $result;
}
function sqlsrv_rollback($conn) : void
{
    \error_clear_last();
    $result = \sqlsrv_rollback($conn);
    if ($result === \false) {
        throw SqlsrvException::createFromPhpError();
    }
}
