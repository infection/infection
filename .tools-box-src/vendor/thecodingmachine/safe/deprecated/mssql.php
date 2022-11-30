<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\MssqlException;
function mssql_bind($stmt, string $param_name, &$var, int $type, bool $is_output = \false, bool $is_null = \false, int $maxlen = -1) : void
{
    \error_clear_last();
    $result = \mssql_bind($stmt, $param_name, $var, $type, $is_output, $is_null, $maxlen);
    if ($result === \false) {
        throw MssqlException::createFromPhpError();
    }
}
function mssql_close($link_identifier = null) : void
{
    \error_clear_last();
    if ($link_identifier !== null) {
        $result = \mssql_close($link_identifier);
    } else {
        $result = \mssql_close();
    }
    if ($result === \false) {
        throw MssqlException::createFromPhpError();
    }
}
function mssql_connect(string $servername = null, string $username = null, string $password = null, bool $new_link = \false)
{
    \error_clear_last();
    if ($new_link !== \false) {
        $result = \mssql_connect($servername, $username, $password, $new_link);
    } elseif ($password !== null) {
        $result = \mssql_connect($servername, $username, $password);
    } elseif ($username !== null) {
        $result = \mssql_connect($servername, $username);
    } elseif ($servername !== null) {
        $result = \mssql_connect($servername);
    } else {
        $result = \mssql_connect();
    }
    if ($result === \false) {
        throw MssqlException::createFromPhpError();
    }
    return $result;
}
function mssql_data_seek($result_identifier, int $row_number) : void
{
    \error_clear_last();
    $result = \mssql_data_seek($result_identifier, $row_number);
    if ($result === \false) {
        throw MssqlException::createFromPhpError();
    }
}
function mssql_field_length($result, int $offset = -1) : int
{
    \error_clear_last();
    $result = \mssql_field_length($result, $offset);
    if ($result === \false) {
        throw MssqlException::createFromPhpError();
    }
    return $result;
}
function mssql_field_name($result, int $offset = -1) : string
{
    \error_clear_last();
    $result = \mssql_field_name($result, $offset);
    if ($result === \false) {
        throw MssqlException::createFromPhpError();
    }
    return $result;
}
function mssql_field_seek($result, int $field_offset) : void
{
    \error_clear_last();
    $result = \mssql_field_seek($result, $field_offset);
    if ($result === \false) {
        throw MssqlException::createFromPhpError();
    }
}
function mssql_field_type($result, int $offset = -1) : string
{
    \error_clear_last();
    $result = \mssql_field_type($result, $offset);
    if ($result === \false) {
        throw MssqlException::createFromPhpError();
    }
    return $result;
}
function mssql_free_result($result) : void
{
    \error_clear_last();
    $result = \mssql_free_result($result);
    if ($result === \false) {
        throw MssqlException::createFromPhpError();
    }
}
function mssql_free_statement($stmt) : void
{
    \error_clear_last();
    $result = \mssql_free_statement($stmt);
    if ($result === \false) {
        throw MssqlException::createFromPhpError();
    }
}
function mssql_init(string $sp_name, $link_identifier = null)
{
    \error_clear_last();
    if ($link_identifier !== null) {
        $result = \mssql_init($sp_name, $link_identifier);
    } else {
        $result = \mssql_init($sp_name);
    }
    if ($result === \false) {
        throw MssqlException::createFromPhpError();
    }
    return $result;
}
function mssql_pconnect(string $servername = null, string $username = null, string $password = null, bool $new_link = \false)
{
    \error_clear_last();
    if ($new_link !== \false) {
        $result = \mssql_pconnect($servername, $username, $password, $new_link);
    } elseif ($password !== null) {
        $result = \mssql_pconnect($servername, $username, $password);
    } elseif ($username !== null) {
        $result = \mssql_pconnect($servername, $username);
    } elseif ($servername !== null) {
        $result = \mssql_pconnect($servername);
    } else {
        $result = \mssql_pconnect();
    }
    if ($result === \false) {
        throw MssqlException::createFromPhpError();
    }
    return $result;
}
function mssql_query(string $query, $link_identifier = null, int $batch_size = 0)
{
    \error_clear_last();
    if ($batch_size !== 0) {
        $result = \mssql_query($query, $link_identifier, $batch_size);
    } elseif ($link_identifier !== null) {
        $result = \mssql_query($query, $link_identifier);
    } else {
        $result = \mssql_query($query);
    }
    if ($result === \false) {
        throw MssqlException::createFromPhpError();
    }
    return $result;
}
function mssql_select_db(string $database_name, $link_identifier = null) : void
{
    \error_clear_last();
    if ($link_identifier !== null) {
        $result = \mssql_select_db($database_name, $link_identifier);
    } else {
        $result = \mssql_select_db($database_name);
    }
    if ($result === \false) {
        throw MssqlException::createFromPhpError();
    }
}
