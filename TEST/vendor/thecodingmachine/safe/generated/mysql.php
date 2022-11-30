<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\MysqlException;
function mysql_close($link_identifier = null) : void
{
    \error_clear_last();
    $result = \mysql_close($link_identifier);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
}
function mysql_connect(string $server = null, string $username = null, string $password = null, bool $new_link = \false, int $client_flags = 0)
{
    \error_clear_last();
    if ($client_flags !== 0) {
        $result = \mysql_connect($server, $username, $password, $new_link, $client_flags);
    } elseif ($new_link !== \false) {
        $result = \mysql_connect($server, $username, $password, $new_link);
    } elseif ($password !== null) {
        $result = \mysql_connect($server, $username, $password);
    } elseif ($username !== null) {
        $result = \mysql_connect($server, $username);
    } elseif ($server !== null) {
        $result = \mysql_connect($server);
    } else {
        $result = \mysql_connect();
    }
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
    return $result;
}
function mysql_create_db(string $database_name, $link_identifier = null) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\mysql_create_db($database_name, $link_identifier);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
}
function mysql_data_seek($result, int $row_number) : void
{
    \error_clear_last();
    $result = \mysql_data_seek($result, $row_number);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
}
function mysql_db_name($result, int $row, $field = null) : string
{
    \error_clear_last();
    $result = \mysql_db_name($result, $row, $field);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
    return $result;
}
function mysql_db_query(string $database, string $query, $link_identifier = null)
{
    \error_clear_last();
    $result = \mysql_db_query($database, $query, $link_identifier);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
    return $result;
}
function mysql_drop_db(string $database_name, $link_identifier = null) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\mysql_drop_db($database_name, $link_identifier);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
}
function mysql_fetch_lengths($result) : array
{
    \error_clear_last();
    $result = \mysql_fetch_lengths($result);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
    return $result;
}
function mysql_field_flags($result, int $field_offset) : string
{
    \error_clear_last();
    $result = \mysql_field_flags($result, $field_offset);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
    return $result;
}
function mysql_field_len($result, int $field_offset) : int
{
    \error_clear_last();
    $result = \mysql_field_len($result, $field_offset);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
    return $result;
}
function mysql_field_name($result, int $field_offset) : string
{
    \error_clear_last();
    $result = \mysql_field_name($result, $field_offset);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
    return $result;
}
function mysql_field_seek($result, int $field_offset) : void
{
    \error_clear_last();
    $result = \mysql_field_seek($result, $field_offset);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
}
function mysql_free_result($result) : void
{
    \error_clear_last();
    $result = \mysql_free_result($result);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
}
function mysql_get_host_info($link_identifier = null) : string
{
    \error_clear_last();
    $result = \mysql_get_host_info($link_identifier);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
    return $result;
}
function mysql_get_proto_info($link_identifier = null) : int
{
    \error_clear_last();
    $result = \mysql_get_proto_info($link_identifier);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
    return $result;
}
function mysql_get_server_info($link_identifier = null) : string
{
    \error_clear_last();
    $result = \mysql_get_server_info($link_identifier);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
    return $result;
}
function mysql_info($link_identifier = null) : string
{
    \error_clear_last();
    $result = \mysql_info($link_identifier);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
    return $result;
}
function mysql_list_dbs($link_identifier = null)
{
    \error_clear_last();
    $result = \mysql_list_dbs($link_identifier);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
    return $result;
}
function mysql_list_fields(string $database_name, string $table_name, $link_identifier = null)
{
    \error_clear_last();
    $result = \mysql_list_fields($database_name, $table_name, $link_identifier);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
    return $result;
}
function mysql_list_processes($link_identifier = null)
{
    \error_clear_last();
    $result = \mysql_list_processes($link_identifier);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
    return $result;
}
function mysql_list_tables(string $database, $link_identifier = null)
{
    \error_clear_last();
    $result = \mysql_list_tables($database, $link_identifier);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
    return $result;
}
function mysql_num_fields($result) : int
{
    \error_clear_last();
    $result = \mysql_num_fields($result);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
    return $result;
}
function mysql_num_rows($result) : int
{
    \error_clear_last();
    $result = \mysql_num_rows($result);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
    return $result;
}
function mysql_query(string $query, $link_identifier = null)
{
    \error_clear_last();
    $result = \mysql_query($query, $link_identifier);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
    return $result;
}
function mysql_real_escape_string(string $unescaped_string, $link_identifier = null) : string
{
    \error_clear_last();
    $result = \mysql_real_escape_string($unescaped_string, $link_identifier);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
    return $result;
}
function mysql_result($result, int $row, $field = 0) : string
{
    \error_clear_last();
    $result = \mysql_result($result, $row, $field);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
    return $result;
}
function mysql_select_db(string $database_name, $link_identifier = null) : void
{
    \error_clear_last();
    $result = \mysql_select_db($database_name, $link_identifier);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
}
function mysql_set_charset(string $charset, $link_identifier = null) : void
{
    \error_clear_last();
    $result = \mysql_set_charset($charset, $link_identifier);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
}
function mysql_tablename($result, int $i) : string
{
    \error_clear_last();
    $result = \mysql_tablename($result, $i);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
    return $result;
}
function mysql_thread_id($link_identifier = null) : int
{
    \error_clear_last();
    $result = \mysql_thread_id($link_identifier);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
    return $result;
}
function mysql_unbuffered_query(string $query, $link_identifier = null)
{
    \error_clear_last();
    $result = \mysql_unbuffered_query($query, $link_identifier);
    if ($result === \false) {
        throw MysqlException::createFromPhpError();
    }
    return $result;
}
