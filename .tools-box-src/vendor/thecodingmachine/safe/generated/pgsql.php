<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\PgsqlException;
function pg_cancel_query($connection) : void
{
    \error_clear_last();
    $safeResult = \pg_cancel_query($connection);
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_connect(string $connection_string, int $flags = 0)
{
    \error_clear_last();
    $safeResult = \pg_connect($connection_string, $flags);
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $safeResult;
}
function pg_connection_reset($connection) : void
{
    \error_clear_last();
    $safeResult = \pg_connection_reset($connection);
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_convert($connection, string $table_name, array $values, int $flags = 0) : array
{
    \error_clear_last();
    $safeResult = \pg_convert($connection, $table_name, $values, $flags);
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $safeResult;
}
function pg_copy_from($connection, string $table_name, array $rows, string $separator = "\t", string $null_as = "\\\\N") : void
{
    \error_clear_last();
    $safeResult = \pg_copy_from($connection, $table_name, $rows, $separator, $null_as);
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_copy_to($connection, string $table_name, string $separator = "\t", string $null_as = "\\\\N") : array
{
    \error_clear_last();
    $safeResult = \pg_copy_to($connection, $table_name, $separator, $null_as);
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $safeResult;
}
function pg_delete($connection, string $table_name, array $conditions, int $flags = \PGSQL_DML_EXEC)
{
    \error_clear_last();
    $safeResult = \pg_delete($connection, $table_name, $conditions, $flags);
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $safeResult;
}
function pg_end_copy($connection = null) : void
{
    \error_clear_last();
    if ($connection !== null) {
        $safeResult = \pg_end_copy($connection);
    } else {
        $safeResult = \pg_end_copy();
    }
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_execute($connection = null, string $stmtname = null, array $params = null)
{
    \error_clear_last();
    if ($params !== null) {
        $safeResult = \pg_execute($connection, $stmtname, $params);
    } elseif ($stmtname !== null) {
        $safeResult = \pg_execute($connection, $stmtname);
    } elseif ($connection !== null) {
        $safeResult = \pg_execute($connection);
    } else {
        $safeResult = \pg_execute();
    }
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $safeResult;
}
function pg_field_table($result, int $field, bool $oid_only = \false)
{
    \error_clear_last();
    $safeResult = \pg_field_table($result, $field, $oid_only);
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $safeResult;
}
function pg_flush($connection)
{
    \error_clear_last();
    $safeResult = \pg_flush($connection);
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $safeResult;
}
function pg_free_result($result) : void
{
    \error_clear_last();
    $safeResult = \pg_free_result($result);
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_host($connection = null) : string
{
    \error_clear_last();
    if ($connection !== null) {
        $safeResult = \pg_host($connection);
    } else {
        $safeResult = \pg_host();
    }
    if ($safeResult === '') {
        throw PgsqlException::createFromPhpError();
    }
    return $safeResult;
}
function pg_insert($connection, string $table_name, array $values, int $flags = \PGSQL_DML_EXEC)
{
    \error_clear_last();
    $safeResult = \pg_insert($connection, $table_name, $values, $flags);
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $safeResult;
}
function pg_last_oid($result) : string
{
    \error_clear_last();
    $safeResult = \pg_last_oid($result);
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $safeResult;
}
function pg_lo_close($lob) : void
{
    \error_clear_last();
    $safeResult = \pg_lo_close($lob);
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_lo_export($connection = null, int $oid = null, string $pathname = null) : void
{
    \error_clear_last();
    if ($pathname !== null) {
        $safeResult = \pg_lo_export($connection, $oid, $pathname);
    } elseif ($oid !== null) {
        $safeResult = \pg_lo_export($connection, $oid);
    } elseif ($connection !== null) {
        $safeResult = \pg_lo_export($connection);
    } else {
        $safeResult = \pg_lo_export();
    }
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_lo_import($connection = null, string $pathname = null, $object_id = null) : int
{
    \error_clear_last();
    if ($object_id !== null) {
        $safeResult = \pg_lo_import($connection, $pathname, $object_id);
    } elseif ($pathname !== null) {
        $safeResult = \pg_lo_import($connection, $pathname);
    } elseif ($connection !== null) {
        $safeResult = \pg_lo_import($connection);
    } else {
        $safeResult = \pg_lo_import();
    }
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $safeResult;
}
function pg_lo_open($connection, int $oid, string $mode)
{
    \error_clear_last();
    $safeResult = \pg_lo_open($connection, $oid, $mode);
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $safeResult;
}
function pg_lo_read($lob, int $length = 8192) : string
{
    \error_clear_last();
    $safeResult = \pg_lo_read($lob, $length);
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $safeResult;
}
function pg_lo_seek($lob, int $offset, int $whence = \SEEK_CUR) : void
{
    \error_clear_last();
    $safeResult = \pg_lo_seek($lob, $offset, $whence);
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_lo_truncate($lob, int $size) : void
{
    \error_clear_last();
    $safeResult = \pg_lo_truncate($lob, $size);
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_lo_unlink($connection, int $oid) : void
{
    \error_clear_last();
    $safeResult = \pg_lo_unlink($connection, $oid);
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_lo_write($lob, string $data, int $length = null) : int
{
    \error_clear_last();
    if ($length !== null) {
        $safeResult = \pg_lo_write($lob, $data, $length);
    } else {
        $safeResult = \pg_lo_write($lob, $data);
    }
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $safeResult;
}
function pg_meta_data($connection, string $table_name, bool $extended = \false) : array
{
    \error_clear_last();
    $safeResult = \pg_meta_data($connection, $table_name, $extended);
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $safeResult;
}
function pg_parameter_status($connection = null, string $param_name = null) : string
{
    \error_clear_last();
    if ($param_name !== null) {
        $safeResult = \pg_parameter_status($connection, $param_name);
    } elseif ($connection !== null) {
        $safeResult = \pg_parameter_status($connection);
    } else {
        $safeResult = \pg_parameter_status();
    }
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $safeResult;
}
function pg_pconnect(string $connection_string, int $flags = 0)
{
    \error_clear_last();
    $safeResult = \pg_pconnect($connection_string, $flags);
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $safeResult;
}
function pg_ping($connection = null) : void
{
    \error_clear_last();
    if ($connection !== null) {
        $safeResult = \pg_ping($connection);
    } else {
        $safeResult = \pg_ping();
    }
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_prepare($connection = null, string $stmtname = null, string $query = null)
{
    \error_clear_last();
    if ($query !== null) {
        $safeResult = \pg_prepare($connection, $stmtname, $query);
    } elseif ($stmtname !== null) {
        $safeResult = \pg_prepare($connection, $stmtname);
    } elseif ($connection !== null) {
        $safeResult = \pg_prepare($connection);
    } else {
        $safeResult = \pg_prepare();
    }
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $safeResult;
}
function pg_put_line($connection = null, string $data = null) : void
{
    \error_clear_last();
    if ($data !== null) {
        $safeResult = \pg_put_line($connection, $data);
    } elseif ($connection !== null) {
        $safeResult = \pg_put_line($connection);
    } else {
        $safeResult = \pg_put_line();
    }
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_query_params($connection = null, string $query = null, array $params = null)
{
    \error_clear_last();
    if ($params !== null) {
        $safeResult = \pg_query_params($connection, $query, $params);
    } elseif ($query !== null) {
        $safeResult = \pg_query_params($connection, $query);
    } elseif ($connection !== null) {
        $safeResult = \pg_query_params($connection);
    } else {
        $safeResult = \pg_query_params();
    }
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $safeResult;
}
function pg_query($connection = null, string $query = null)
{
    \error_clear_last();
    if ($query !== null) {
        $safeResult = \pg_query($connection, $query);
    } elseif ($connection !== null) {
        $safeResult = \pg_query($connection);
    } else {
        $safeResult = \pg_query();
    }
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $safeResult;
}
function pg_result_error_field($result, int $field_code) : ?string
{
    \error_clear_last();
    $safeResult = \pg_result_error_field($result, $field_code);
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $safeResult;
}
function pg_result_seek($result, int $row) : void
{
    \error_clear_last();
    $safeResult = \pg_result_seek($result, $row);
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_select($connection, string $table_name, array $conditions, int $flags = \PGSQL_DML_EXEC, int $mode = \PGSQL_ASSOC)
{
    \error_clear_last();
    $safeResult = \pg_select($connection, $table_name, $conditions, $flags, $mode);
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $safeResult;
}
function pg_socket($connection)
{
    \error_clear_last();
    $safeResult = \pg_socket($connection);
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $safeResult;
}
function pg_trace(string $filename, string $mode = "w", $connection = null) : void
{
    \error_clear_last();
    if ($connection !== null) {
        $safeResult = \pg_trace($filename, $mode, $connection);
    } else {
        $safeResult = \pg_trace($filename, $mode);
    }
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_update($connection, string $table_name, array $values, array $conditions, int $flags = \PGSQL_DML_EXEC)
{
    \error_clear_last();
    $safeResult = \pg_update($connection, $table_name, $values, $conditions, $flags);
    if ($safeResult === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $safeResult;
}
