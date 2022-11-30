<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\CubridException;
function cubrid_bind($req_identifier, int $bind_index, $bind_value, string $bind_value_type = null) : void
{
    \error_clear_last();
    if ($bind_value_type !== null) {
        $result = \cubrid_bind($req_identifier, $bind_index, $bind_value, $bind_value_type);
    } else {
        $result = \cubrid_bind($req_identifier, $bind_index, $bind_value);
    }
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
function cubrid_col_size($conn_identifier, string $oid, string $attr_name) : int
{
    \error_clear_last();
    $result = \cubrid_col_size($conn_identifier, $oid, $attr_name);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
    return $result;
}
function cubrid_column_names($req_identifier) : array
{
    \error_clear_last();
    $result = \cubrid_column_names($req_identifier);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
    return $result;
}
function cubrid_column_types($req_identifier) : array
{
    \error_clear_last();
    $result = \cubrid_column_types($req_identifier);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
    return $result;
}
function cubrid_commit($conn_identifier) : void
{
    \error_clear_last();
    $result = \cubrid_commit($conn_identifier);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
function cubrid_connect_with_url(string $conn_url, string $userid = null, string $passwd = null, bool $new_link = \false)
{
    \error_clear_last();
    if ($new_link !== \false) {
        $result = \cubrid_connect_with_url($conn_url, $userid, $passwd, $new_link);
    } elseif ($passwd !== null) {
        $result = \cubrid_connect_with_url($conn_url, $userid, $passwd);
    } elseif ($userid !== null) {
        $result = \cubrid_connect_with_url($conn_url, $userid);
    } else {
        $result = \cubrid_connect_with_url($conn_url);
    }
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
    return $result;
}
function cubrid_connect(string $host, int $port, string $dbname, string $userid = null, string $passwd = null, bool $new_link = \false)
{
    \error_clear_last();
    if ($new_link !== \false) {
        $result = \cubrid_connect($host, $port, $dbname, $userid, $passwd, $new_link);
    } elseif ($passwd !== null) {
        $result = \cubrid_connect($host, $port, $dbname, $userid, $passwd);
    } elseif ($userid !== null) {
        $result = \cubrid_connect($host, $port, $dbname, $userid);
    } else {
        $result = \cubrid_connect($host, $port, $dbname);
    }
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
    return $result;
}
function cubrid_current_oid($req_identifier) : string
{
    \error_clear_last();
    $result = \cubrid_current_oid($req_identifier);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
    return $result;
}
function cubrid_disconnect($conn_identifier = null) : void
{
    \error_clear_last();
    if ($conn_identifier !== null) {
        $result = \cubrid_disconnect($conn_identifier);
    } else {
        $result = \cubrid_disconnect();
    }
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
function cubrid_drop($conn_identifier, string $oid) : void
{
    \error_clear_last();
    $result = \cubrid_drop($conn_identifier, $oid);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
function cubrid_free_result($req_identifier) : void
{
    \error_clear_last();
    $result = \cubrid_free_result($req_identifier);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
function cubrid_get_charset($conn_identifier) : string
{
    \error_clear_last();
    $result = \cubrid_get_charset($conn_identifier);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
    return $result;
}
function cubrid_get_class_name($conn_identifier, string $oid) : string
{
    \error_clear_last();
    $result = \cubrid_get_class_name($conn_identifier, $oid);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
    return $result;
}
function cubrid_get_client_info() : string
{
    \error_clear_last();
    $result = \cubrid_get_client_info();
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
    return $result;
}
function cubrid_get_db_parameter($conn_identifier) : array
{
    \error_clear_last();
    $result = \cubrid_get_db_parameter($conn_identifier);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
    return $result;
}
function cubrid_get_query_timeout($req_identifier) : int
{
    \error_clear_last();
    $result = \cubrid_get_query_timeout($req_identifier);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
    return $result;
}
function cubrid_get_server_info($conn_identifier) : string
{
    \error_clear_last();
    $result = \cubrid_get_server_info($conn_identifier);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
    return $result;
}
function cubrid_insert_id($conn_identifier = null) : string
{
    \error_clear_last();
    if ($conn_identifier !== null) {
        $result = \cubrid_insert_id($conn_identifier);
    } else {
        $result = \cubrid_insert_id();
    }
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
    return $result;
}
function cubrid_lob_close(array $lob_identifier_array) : void
{
    \error_clear_last();
    $result = \cubrid_lob_close($lob_identifier_array);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
function cubrid_lob_export($conn_identifier, $lob_identifier, string $path_name) : void
{
    \error_clear_last();
    $result = \cubrid_lob_export($conn_identifier, $lob_identifier, $path_name);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
function cubrid_lob_get($conn_identifier, string $sql) : array
{
    \error_clear_last();
    $result = \cubrid_lob_get($conn_identifier, $sql);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
    return $result;
}
function cubrid_lob_send($conn_identifier, $lob_identifier) : void
{
    \error_clear_last();
    $result = \cubrid_lob_send($conn_identifier, $lob_identifier);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
function cubrid_lob_size($lob_identifier) : string
{
    \error_clear_last();
    $result = \cubrid_lob_size($lob_identifier);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
    return $result;
}
function cubrid_lob2_bind($req_identifier, int $bind_index, $bind_value, string $bind_value_type = null) : void
{
    \error_clear_last();
    if ($bind_value_type !== null) {
        $result = \cubrid_lob2_bind($req_identifier, $bind_index, $bind_value, $bind_value_type);
    } else {
        $result = \cubrid_lob2_bind($req_identifier, $bind_index, $bind_value);
    }
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
function cubrid_lob2_close($lob_identifier) : void
{
    \error_clear_last();
    $result = \cubrid_lob2_close($lob_identifier);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
function cubrid_lob2_export($lob_identifier, string $file_name) : void
{
    \error_clear_last();
    $result = \cubrid_lob2_export($lob_identifier, $file_name);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
function cubrid_lob2_import($lob_identifier, string $file_name) : void
{
    \error_clear_last();
    $result = \cubrid_lob2_import($lob_identifier, $file_name);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
function cubrid_lob2_new($conn_identifier = null, string $type = "BLOB")
{
    \error_clear_last();
    if ($type !== "BLOB") {
        $result = \cubrid_lob2_new($conn_identifier, $type);
    } elseif ($conn_identifier !== null) {
        $result = \cubrid_lob2_new($conn_identifier);
    } else {
        $result = \cubrid_lob2_new();
    }
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
    return $result;
}
function cubrid_lob2_read($lob_identifier, int $len) : string
{
    \error_clear_last();
    $result = \cubrid_lob2_read($lob_identifier, $len);
    if ($result === null) {
        throw CubridException::createFromPhpError();
    }
    return $result;
}
function cubrid_lob2_seek($lob_identifier, int $offset, int $origin = \CUBRID_CURSOR_CURRENT) : void
{
    \error_clear_last();
    $result = \cubrid_lob2_seek($lob_identifier, $offset, $origin);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
function cubrid_lob2_seek64($lob_identifier, string $offset, int $origin = \CUBRID_CURSOR_CURRENT) : void
{
    \error_clear_last();
    $result = \cubrid_lob2_seek64($lob_identifier, $offset, $origin);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
function cubrid_lob2_size($lob_identifier) : int
{
    \error_clear_last();
    $result = \cubrid_lob2_size($lob_identifier);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
    return $result;
}
function cubrid_lob2_size64($lob_identifier) : string
{
    \error_clear_last();
    $result = \cubrid_lob2_size64($lob_identifier);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
    return $result;
}
function cubrid_lob2_tell($lob_identifier) : int
{
    \error_clear_last();
    $result = \cubrid_lob2_tell($lob_identifier);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
    return $result;
}
function cubrid_lob2_tell64($lob_identifier) : string
{
    \error_clear_last();
    $result = \cubrid_lob2_tell64($lob_identifier);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
    return $result;
}
function cubrid_lob2_write($lob_identifier, string $buf) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\cubrid_lob2_write($lob_identifier, $buf);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
function cubrid_lock_read($conn_identifier, string $oid) : void
{
    \error_clear_last();
    $result = \cubrid_lock_read($conn_identifier, $oid);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
function cubrid_lock_write($conn_identifier, string $oid) : void
{
    \error_clear_last();
    $result = \cubrid_lock_write($conn_identifier, $oid);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
function cubrid_move_cursor($req_identifier, int $offset, int $origin = \CUBRID_CURSOR_CURRENT) : int
{
    \error_clear_last();
    $result = \cubrid_move_cursor($req_identifier, $offset, $origin);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
    return $result;
}
function cubrid_next_result($result) : void
{
    \error_clear_last();
    $result = \cubrid_next_result($result);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
function cubrid_pconnect_with_url(string $conn_url, string $userid = null, string $passwd = null)
{
    \error_clear_last();
    if ($passwd !== null) {
        $result = \cubrid_pconnect_with_url($conn_url, $userid, $passwd);
    } elseif ($userid !== null) {
        $result = \cubrid_pconnect_with_url($conn_url, $userid);
    } else {
        $result = \cubrid_pconnect_with_url($conn_url);
    }
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
    return $result;
}
function cubrid_pconnect(string $host, int $port, string $dbname, string $userid = null, string $passwd = null)
{
    \error_clear_last();
    if ($passwd !== null) {
        $result = \cubrid_pconnect($host, $port, $dbname, $userid, $passwd);
    } elseif ($userid !== null) {
        $result = \cubrid_pconnect($host, $port, $dbname, $userid);
    } else {
        $result = \cubrid_pconnect($host, $port, $dbname);
    }
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
    return $result;
}
function cubrid_prepare($conn_identifier, string $prepare_stmt, int $option = 0)
{
    \error_clear_last();
    $result = \cubrid_prepare($conn_identifier, $prepare_stmt, $option);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
    return $result;
}
function cubrid_put($conn_identifier, string $oid, string $attr = null, $value = null) : void
{
    \error_clear_last();
    if ($value !== null) {
        $result = \cubrid_put($conn_identifier, $oid, $attr, $value);
    } elseif ($attr !== null) {
        $result = \cubrid_put($conn_identifier, $oid, $attr);
    } else {
        $result = \cubrid_put($conn_identifier, $oid);
    }
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
function cubrid_rollback($conn_identifier) : void
{
    \error_clear_last();
    $result = \cubrid_rollback($conn_identifier);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
function cubrid_schema($conn_identifier, int $schema_type, string $class_name = null, string $attr_name = null) : array
{
    \error_clear_last();
    if ($attr_name !== null) {
        $result = \cubrid_schema($conn_identifier, $schema_type, $class_name, $attr_name);
    } elseif ($class_name !== null) {
        $result = \cubrid_schema($conn_identifier, $schema_type, $class_name);
    } else {
        $result = \cubrid_schema($conn_identifier, $schema_type);
    }
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
    return $result;
}
function cubrid_seq_drop($conn_identifier, string $oid, string $attr_name, int $index) : void
{
    \error_clear_last();
    $result = \cubrid_seq_drop($conn_identifier, $oid, $attr_name, $index);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
function cubrid_seq_insert($conn_identifier, string $oid, string $attr_name, int $index, string $seq_element) : void
{
    \error_clear_last();
    $result = \cubrid_seq_insert($conn_identifier, $oid, $attr_name, $index, $seq_element);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
function cubrid_seq_put($conn_identifier, string $oid, string $attr_name, int $index, string $seq_element) : void
{
    \error_clear_last();
    $result = \cubrid_seq_put($conn_identifier, $oid, $attr_name, $index, $seq_element);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
function cubrid_set_add($conn_identifier, string $oid, string $attr_name, string $set_element) : void
{
    \error_clear_last();
    $result = \cubrid_set_add($conn_identifier, $oid, $attr_name, $set_element);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
function cubrid_set_autocommit($conn_identifier, bool $mode) : void
{
    \error_clear_last();
    $result = \cubrid_set_autocommit($conn_identifier, $mode);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
function cubrid_set_db_parameter($conn_identifier, int $param_type, int $param_value) : void
{
    \error_clear_last();
    $result = \cubrid_set_db_parameter($conn_identifier, $param_type, $param_value);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
function cubrid_set_drop($conn_identifier, string $oid, string $attr_name, string $set_element) : void
{
    \error_clear_last();
    $result = \cubrid_set_drop($conn_identifier, $oid, $attr_name, $set_element);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
function cubrid_set_query_timeout($req_identifier, int $timeout) : void
{
    \error_clear_last();
    $result = \cubrid_set_query_timeout($req_identifier, $timeout);
    if ($result === \false) {
        throw CubridException::createFromPhpError();
    }
}
