<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\Oci8Exception;
function oci_bind_array_by_name($statement, string $param, array &$var, int $max_array_length, int $max_item_length = -1, int $type = \SQLT_AFC) : void
{
    \error_clear_last();
    $safeResult = \oci_bind_array_by_name($statement, $param, $var, $max_array_length, $max_item_length, $type);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
}
function oci_bind_by_name($statement, string $param, &$var, int $max_length = -1, int $type = 0) : void
{
    \error_clear_last();
    $safeResult = \oci_bind_by_name($statement, $param, $var, $max_length, $type);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
}
function oci_cancel($statement) : void
{
    \error_clear_last();
    $safeResult = \oci_cancel($statement);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
}
function oci_commit($connection) : void
{
    \error_clear_last();
    $safeResult = \oci_commit($connection);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
}
function oci_connect(string $username, string $password, string $connection_string = null, string $encoding = "", int $session_mode = \OCI_DEFAULT)
{
    \error_clear_last();
    if ($session_mode !== \OCI_DEFAULT) {
        $safeResult = \oci_connect($username, $password, $connection_string, $encoding, $session_mode);
    } elseif ($encoding !== "") {
        $safeResult = \oci_connect($username, $password, $connection_string, $encoding);
    } elseif ($connection_string !== null) {
        $safeResult = \oci_connect($username, $password, $connection_string);
    } else {
        $safeResult = \oci_connect($username, $password);
    }
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
    return $safeResult;
}
function oci_define_by_name($statement, string $column, &$var, int $type = 0) : void
{
    \error_clear_last();
    $safeResult = \oci_define_by_name($statement, $column, $var, $type);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
}
function oci_execute($statement, int $mode = \OCI_COMMIT_ON_SUCCESS) : void
{
    \error_clear_last();
    $safeResult = \oci_execute($statement, $mode);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
}
function oci_field_name($statement, $column) : string
{
    \error_clear_last();
    $safeResult = \oci_field_name($statement, $column);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
    return $safeResult;
}
function oci_field_precision($statement, $column) : int
{
    \error_clear_last();
    $safeResult = \oci_field_precision($statement, $column);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
    return $safeResult;
}
function oci_field_scale($statement, $column) : int
{
    \error_clear_last();
    $safeResult = \oci_field_scale($statement, $column);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
    return $safeResult;
}
function oci_field_size($statement, $column) : int
{
    \error_clear_last();
    $safeResult = \oci_field_size($statement, $column);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
    return $safeResult;
}
function oci_field_type_raw($statement, $column) : int
{
    \error_clear_last();
    $safeResult = \oci_field_type_raw($statement, $column);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
    return $safeResult;
}
function oci_field_type($statement, $column)
{
    \error_clear_last();
    $safeResult = \oci_field_type($statement, $column);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
    return $safeResult;
}
function oci_free_descriptor(\OCILob $lob) : void
{
    \error_clear_last();
    $safeResult = \oci_free_descriptor($lob);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
}
function oci_free_statement($statement) : void
{
    \error_clear_last();
    $safeResult = \oci_free_statement($statement);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
}
function oci_new_collection($connection, string $type_name, string $schema = null)
{
    \error_clear_last();
    if ($schema !== null) {
        $safeResult = \oci_new_collection($connection, $type_name, $schema);
    } else {
        $safeResult = \oci_new_collection($connection, $type_name);
    }
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
    return $safeResult;
}
function oci_new_connect(string $username, string $password, string $connection_string = null, string $encoding = "", int $session_mode = \OCI_DEFAULT)
{
    \error_clear_last();
    if ($session_mode !== \OCI_DEFAULT) {
        $safeResult = \oci_new_connect($username, $password, $connection_string, $encoding, $session_mode);
    } elseif ($encoding !== "") {
        $safeResult = \oci_new_connect($username, $password, $connection_string, $encoding);
    } elseif ($connection_string !== null) {
        $safeResult = \oci_new_connect($username, $password, $connection_string);
    } else {
        $safeResult = \oci_new_connect($username, $password);
    }
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
    return $safeResult;
}
function oci_new_cursor($connection)
{
    \error_clear_last();
    $safeResult = \oci_new_cursor($connection);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
    return $safeResult;
}
function oci_new_descriptor($connection, int $type = \OCI_DTYPE_LOB)
{
    \error_clear_last();
    $safeResult = \oci_new_descriptor($connection, $type);
    if ($safeResult === null) {
        throw Oci8Exception::createFromPhpError();
    }
    return $safeResult;
}
function oci_num_rows($statement) : int
{
    \error_clear_last();
    $safeResult = \oci_num_rows($statement);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
    return $safeResult;
}
function oci_parse($connection, string $sql)
{
    \error_clear_last();
    $safeResult = \oci_parse($connection, $sql);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
    return $safeResult;
}
function oci_pconnect(string $username, string $password, string $connection_string = null, string $encoding = "", int $session_mode = \OCI_DEFAULT)
{
    \error_clear_last();
    if ($session_mode !== \OCI_DEFAULT) {
        $safeResult = \oci_pconnect($username, $password, $connection_string, $encoding, $session_mode);
    } elseif ($encoding !== "") {
        $safeResult = \oci_pconnect($username, $password, $connection_string, $encoding);
    } elseif ($connection_string !== null) {
        $safeResult = \oci_pconnect($username, $password, $connection_string);
    } else {
        $safeResult = \oci_pconnect($username, $password);
    }
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
    return $safeResult;
}
function oci_register_taf_callback($connection, callable $callback) : void
{
    \error_clear_last();
    $safeResult = \oci_register_taf_callback($connection, $callback);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
}
function oci_result($statement, $column) : string
{
    \error_clear_last();
    $safeResult = \oci_result($statement, $column);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
    return $safeResult;
}
function oci_rollback($connection) : void
{
    \error_clear_last();
    $safeResult = \oci_rollback($connection);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
}
function oci_server_version($connection) : string
{
    \error_clear_last();
    $safeResult = \oci_server_version($connection);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
    return $safeResult;
}
function oci_set_action($connection, string $action) : void
{
    \error_clear_last();
    $safeResult = \oci_set_action($connection, $action);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
}
function oci_set_call_timeout($connection, int $timeout) : void
{
    \error_clear_last();
    $safeResult = \oci_set_call_timeout($connection, $timeout);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
}
function oci_set_client_identifier($connection, string $client_id) : void
{
    \error_clear_last();
    $safeResult = \oci_set_client_identifier($connection, $client_id);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
}
function oci_set_client_info($connection, string $client_info) : void
{
    \error_clear_last();
    $safeResult = \oci_set_client_info($connection, $client_info);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
}
function oci_set_db_operation($connection, string $action) : void
{
    \error_clear_last();
    $safeResult = \oci_set_db_operation($connection, $action);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
}
function oci_set_edition(string $edition) : void
{
    \error_clear_last();
    $safeResult = \oci_set_edition($edition);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
}
function oci_set_module_name($connection, string $name) : void
{
    \error_clear_last();
    $safeResult = \oci_set_module_name($connection, $name);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
}
function oci_set_prefetch_lob($statement, int $prefetch_lob_size) : void
{
    \error_clear_last();
    $safeResult = \_HumbugBoxb47773b41c19\oci_set_prefetch_lob($statement, $prefetch_lob_size);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
}
function oci_set_prefetch($statement, int $rows) : void
{
    \error_clear_last();
    $safeResult = \oci_set_prefetch($statement, $rows);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
}
function oci_statement_type($statement) : string
{
    \error_clear_last();
    $safeResult = \oci_statement_type($statement);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
    return $safeResult;
}
function oci_unregister_taf_callback($connection) : void
{
    \error_clear_last();
    $safeResult = \oci_unregister_taf_callback($connection);
    if ($safeResult === \false) {
        throw Oci8Exception::createFromPhpError();
    }
}
