<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\PgsqlException;
function pg_cancel_query($connection) : void
{
    \error_clear_last();
    $result = \pg_cancel_query($connection);
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_connect(string $connection_string, int $flags = 0)
{
    \error_clear_last();
    $result = \pg_connect($connection_string, $flags);
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $result;
}
function pg_connection_reset($connection) : void
{
    \error_clear_last();
    $result = \pg_connection_reset($connection);
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_convert($connection, string $table_name, array $values, int $flags = 0) : array
{
    \error_clear_last();
    $result = \pg_convert($connection, $table_name, $values, $flags);
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $result;
}
function pg_copy_from($connection, string $table_name, array $rows, string $separator = "\t", string $null_as = "\\\\N") : void
{
    \error_clear_last();
    $result = \pg_copy_from($connection, $table_name, $rows, $separator, $null_as);
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_copy_to($connection, string $table_name, string $separator = "\t", string $null_as = "\\\\N") : array
{
    \error_clear_last();
    $result = \pg_copy_to($connection, $table_name, $separator, $null_as);
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $result;
}
function pg_delete($connection, string $table_name, array $conditions, int $flags = \PGSQL_DML_EXEC)
{
    \error_clear_last();
    $result = \pg_delete($connection, $table_name, $conditions, $flags);
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $result;
}
function pg_end_copy($connection = null) : void
{
    \error_clear_last();
    if ($connection !== null) {
        $result = \pg_end_copy($connection);
    } else {
        $result = \pg_end_copy();
    }
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_execute($connection = null, string $stmtname = null, array $params = null)
{
    \error_clear_last();
    if ($params !== null) {
        $result = \pg_execute($connection, $stmtname, $params);
    } elseif ($stmtname !== null) {
        $result = \pg_execute($connection, $stmtname);
    } elseif ($connection !== null) {
        $result = \pg_execute($connection);
    } else {
        $result = \pg_execute();
    }
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $result;
}
function pg_field_table($result, int $field, bool $oid_only = \false)
{
    \error_clear_last();
    $result = \pg_field_table($result, $field, $oid_only);
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $result;
}
function pg_flush($connection)
{
    \error_clear_last();
    $result = \pg_flush($connection);
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $result;
}
function pg_free_result($result) : void
{
    \error_clear_last();
    $result = \pg_free_result($result);
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_host($connection = null) : string
{
    \error_clear_last();
    if ($connection !== null) {
        $result = \pg_host($connection);
    } else {
        $result = \pg_host();
    }
    if ($result === '') {
        throw PgsqlException::createFromPhpError();
    }
    return $result;
}
function pg_insert($connection, string $table_name, array $values, int $flags = \PGSQL_DML_EXEC)
{
    \error_clear_last();
    $result = \pg_insert($connection, $table_name, $values, $flags);
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $result;
}
function pg_last_oid($result) : string
{
    \error_clear_last();
    $result = \pg_last_oid($result);
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $result;
}
function pg_lo_close($lob) : void
{
    \error_clear_last();
    $result = \pg_lo_close($lob);
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_lo_export($connection = null, int $oid = null, string $pathname = null) : void
{
    \error_clear_last();
    if ($pathname !== null) {
        $result = \pg_lo_export($connection, $oid, $pathname);
    } elseif ($oid !== null) {
        $result = \pg_lo_export($connection, $oid);
    } elseif ($connection !== null) {
        $result = \pg_lo_export($connection);
    } else {
        $result = \pg_lo_export();
    }
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_lo_import($connection = null, string $pathname = null, $object_id = null) : int
{
    \error_clear_last();
    if ($object_id !== null) {
        $result = \pg_lo_import($connection, $pathname, $object_id);
    } elseif ($pathname !== null) {
        $result = \pg_lo_import($connection, $pathname);
    } elseif ($connection !== null) {
        $result = \pg_lo_import($connection);
    } else {
        $result = \pg_lo_import();
    }
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $result;
}
function pg_lo_open($connection, int $oid, string $mode)
{
    \error_clear_last();
    $result = \pg_lo_open($connection, $oid, $mode);
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $result;
}
function pg_lo_read($lob, int $length = 8192) : string
{
    \error_clear_last();
    $result = \pg_lo_read($lob, $length);
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $result;
}
function pg_lo_seek($lob, int $offset, int $whence = \SEEK_CUR) : void
{
    \error_clear_last();
    $result = \pg_lo_seek($lob, $offset, $whence);
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_lo_truncate($lob, int $size) : void
{
    \error_clear_last();
    $result = \pg_lo_truncate($lob, $size);
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_lo_unlink($connection, int $oid) : void
{
    \error_clear_last();
    $result = \pg_lo_unlink($connection, $oid);
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_lo_write($lob, string $data, int $length = null) : int
{
    \error_clear_last();
    if ($length !== null) {
        $result = \pg_lo_write($lob, $data, $length);
    } else {
        $result = \pg_lo_write($lob, $data);
    }
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $result;
}
function pg_meta_data($connection, string $table_name, bool $extended = \false) : array
{
    \error_clear_last();
    $result = \pg_meta_data($connection, $table_name, $extended);
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $result;
}
function pg_parameter_status($connection = null, string $param_name = null) : string
{
    \error_clear_last();
    if ($param_name !== null) {
        $result = \pg_parameter_status($connection, $param_name);
    } elseif ($connection !== null) {
        $result = \pg_parameter_status($connection);
    } else {
        $result = \pg_parameter_status();
    }
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $result;
}
function pg_pconnect(string $connection_string, int $flags = 0)
{
    \error_clear_last();
    $result = \pg_pconnect($connection_string, $flags);
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $result;
}
function pg_ping($connection = null) : void
{
    \error_clear_last();
    if ($connection !== null) {
        $result = \pg_ping($connection);
    } else {
        $result = \pg_ping();
    }
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_prepare($connection = null, string $stmtname = null, string $query = null)
{
    \error_clear_last();
    if ($query !== null) {
        $result = \pg_prepare($connection, $stmtname, $query);
    } elseif ($stmtname !== null) {
        $result = \pg_prepare($connection, $stmtname);
    } elseif ($connection !== null) {
        $result = \pg_prepare($connection);
    } else {
        $result = \pg_prepare();
    }
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $result;
}
function pg_put_line($connection = null, string $data = null) : void
{
    \error_clear_last();
    if ($data !== null) {
        $result = \pg_put_line($connection, $data);
    } elseif ($connection !== null) {
        $result = \pg_put_line($connection);
    } else {
        $result = \pg_put_line();
    }
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_query_params($connection = null, string $query = null, array $params = null)
{
    \error_clear_last();
    if ($params !== null) {
        $result = \pg_query_params($connection, $query, $params);
    } elseif ($query !== null) {
        $result = \pg_query_params($connection, $query);
    } elseif ($connection !== null) {
        $result = \pg_query_params($connection);
    } else {
        $result = \pg_query_params();
    }
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $result;
}
function pg_query($connection = null, string $query = null)
{
    \error_clear_last();
    if ($query !== null) {
        $result = \pg_query($connection, $query);
    } elseif ($connection !== null) {
        $result = \pg_query($connection);
    } else {
        $result = \pg_query();
    }
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $result;
}
function pg_result_error_field($result, int $field_code) : ?string
{
    \error_clear_last();
    $result = \pg_result_error_field($result, $field_code);
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $result;
}
function pg_result_seek($result, int $row) : void
{
    \error_clear_last();
    $result = \pg_result_seek($result, $row);
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_select($connection, string $table_name, array $conditions, int $flags = \PGSQL_DML_EXEC, int $mode = \PGSQL_ASSOC)
{
    \error_clear_last();
    $result = \pg_select($connection, $table_name, $conditions, $flags, $mode);
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $result;
}
function pg_socket($connection)
{
    \error_clear_last();
    $result = \pg_socket($connection);
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $result;
}
function pg_trace(string $filename, string $mode = "w", $connection = null) : void
{
    \error_clear_last();
    if ($connection !== null) {
        $result = \pg_trace($filename, $mode, $connection);
    } else {
        $result = \pg_trace($filename, $mode);
    }
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
}
function pg_update($connection, string $table_name, array $values, array $conditions, int $flags = \PGSQL_DML_EXEC)
{
    \error_clear_last();
    $result = \pg_update($connection, $table_name, $values, $conditions, $flags);
    if ($result === \false) {
        throw PgsqlException::createFromPhpError();
    }
    return $result;
}
