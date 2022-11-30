<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\UodbcException;
function odbc_autocommit($odbc, bool $enable = \false)
{
    \error_clear_last();
    $result = \odbc_autocommit($odbc, $enable);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $result;
}
function odbc_binmode(int $statement, int $mode) : void
{
    \error_clear_last();
    $result = \odbc_binmode($statement, $mode);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
}
function odbc_columnprivileges($odbc, string $catalog, string $schema, string $table, string $column)
{
    \error_clear_last();
    $result = \odbc_columnprivileges($odbc, $catalog, $schema, $table, $column);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $result;
}
function odbc_columns($odbc, string $catalog = null, string $schema = null, string $table = null, string $column = null)
{
    \error_clear_last();
    if ($column !== null) {
        $result = \odbc_columns($odbc, $catalog, $schema, $table, $column);
    } elseif ($table !== null) {
        $result = \odbc_columns($odbc, $catalog, $schema, $table);
    } elseif ($schema !== null) {
        $result = \odbc_columns($odbc, $catalog, $schema);
    } elseif ($catalog !== null) {
        $result = \odbc_columns($odbc, $catalog);
    } else {
        $result = \odbc_columns($odbc);
    }
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $result;
}
function odbc_commit($odbc) : void
{
    \error_clear_last();
    $result = \odbc_commit($odbc);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
}
function odbc_connect(string $dsn, string $user, string $password, int $cursor_option = \SQL_CUR_USE_DRIVER)
{
    \error_clear_last();
    $result = \odbc_connect($dsn, $user, $password, $cursor_option);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $result;
}
function odbc_cursor($statement) : string
{
    \error_clear_last();
    $result = \odbc_cursor($statement);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $result;
}
function odbc_data_source($odbc, int $fetch_type) : array
{
    \error_clear_last();
    $result = \odbc_data_source($odbc, $fetch_type);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $result;
}
function odbc_exec($odbc, string $query)
{
    \error_clear_last();
    $result = \odbc_exec($odbc, $query);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $result;
}
function odbc_execute($statement, array $params = []) : void
{
    \error_clear_last();
    $result = \odbc_execute($statement, $params);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
}
function odbc_fetch_into($statement, ?array &$array, int $row = 0) : int
{
    \error_clear_last();
    $result = \odbc_fetch_into($statement, $array, $row);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $result;
}
function odbc_field_len($statement, int $field) : int
{
    \error_clear_last();
    $result = \odbc_field_len($statement, $field);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $result;
}
function odbc_field_name($statement, int $field) : string
{
    \error_clear_last();
    $result = \odbc_field_name($statement, $field);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $result;
}
function odbc_field_num($statement, string $field) : int
{
    \error_clear_last();
    $result = \odbc_field_num($statement, $field);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $result;
}
function odbc_field_scale($statement, int $field) : int
{
    \error_clear_last();
    $result = \odbc_field_scale($statement, $field);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $result;
}
function odbc_field_type($statement, int $field) : string
{
    \error_clear_last();
    $result = \odbc_field_type($statement, $field);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $result;
}
function odbc_foreignkeys($odbc, string $pk_catalog, string $pk_schema, string $pk_table, string $fk_catalog, string $fk_schema, string $fk_table)
{
    \error_clear_last();
    $result = \odbc_foreignkeys($odbc, $pk_catalog, $pk_schema, $pk_table, $fk_catalog, $fk_schema, $fk_table);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $result;
}
function odbc_gettypeinfo($odbc, int $data_type = 0)
{
    \error_clear_last();
    $result = \odbc_gettypeinfo($odbc, $data_type);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $result;
}
function odbc_longreadlen($statement, int $length) : void
{
    \error_clear_last();
    $result = \odbc_longreadlen($statement, $length);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
}
function odbc_pconnect(string $dsn, string $user, string $password, int $cursor_option = \SQL_CUR_USE_DRIVER)
{
    \error_clear_last();
    $result = \odbc_pconnect($dsn, $user, $password, $cursor_option);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $result;
}
function odbc_prepare($odbc, string $query)
{
    \error_clear_last();
    $result = \odbc_prepare($odbc, $query);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $result;
}
function odbc_primarykeys($odbc, string $catalog, string $schema, string $table)
{
    \error_clear_last();
    $result = \odbc_primarykeys($odbc, $catalog, $schema, $table);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $result;
}
function odbc_procedurecolumns($odbc, string $catalog = null, string $schema = null, string $procedure = null, string $column = null)
{
    \error_clear_last();
    if ($column !== null) {
        $result = \odbc_procedurecolumns($odbc, $catalog, $schema, $procedure, $column);
    } elseif ($procedure !== null) {
        $result = \odbc_procedurecolumns($odbc, $catalog, $schema, $procedure);
    } elseif ($schema !== null) {
        $result = \odbc_procedurecolumns($odbc, $catalog, $schema);
    } elseif ($catalog !== null) {
        $result = \odbc_procedurecolumns($odbc, $catalog);
    } else {
        $result = \odbc_procedurecolumns($odbc);
    }
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $result;
}
function odbc_procedures($odbc, string $catalog = null, string $schema = null, string $procedure = null)
{
    \error_clear_last();
    if ($procedure !== null) {
        $result = \odbc_procedures($odbc, $catalog, $schema, $procedure);
    } elseif ($schema !== null) {
        $result = \odbc_procedures($odbc, $catalog, $schema);
    } elseif ($catalog !== null) {
        $result = \odbc_procedures($odbc, $catalog);
    } else {
        $result = \odbc_procedures($odbc);
    }
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $result;
}
function odbc_result_all($statement, string $format = "") : int
{
    \error_clear_last();
    $result = \odbc_result_all($statement, $format);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $result;
}
function odbc_result($statement, $field)
{
    \error_clear_last();
    $result = \odbc_result($statement, $field);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $result;
}
function odbc_rollback($odbc) : void
{
    \error_clear_last();
    $result = \odbc_rollback($odbc);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
}
function odbc_setoption($odbc, int $which, int $option, int $value) : void
{
    \error_clear_last();
    $result = \odbc_setoption($odbc, $which, $option, $value);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
}
function odbc_specialcolumns($odbc, int $type, string $catalog, string $schema, string $table, int $scope, int $nullable)
{
    \error_clear_last();
    $result = \odbc_specialcolumns($odbc, $type, $catalog, $schema, $table, $scope, $nullable);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $result;
}
function odbc_statistics($odbc, string $catalog, string $schema, string $table, int $unique, int $accuracy)
{
    \error_clear_last();
    $result = \odbc_statistics($odbc, $catalog, $schema, $table, $unique, $accuracy);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $result;
}
function odbc_tableprivileges($odbc, string $catalog, string $schema, string $table)
{
    \error_clear_last();
    $result = \odbc_tableprivileges($odbc, $catalog, $schema, $table);
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $result;
}
function odbc_tables($odbc, string $catalog = null, string $schema = null, string $table = null, string $types = null)
{
    \error_clear_last();
    if ($types !== null) {
        $result = \odbc_tables($odbc, $catalog, $schema, $table, $types);
    } elseif ($table !== null) {
        $result = \odbc_tables($odbc, $catalog, $schema, $table);
    } elseif ($schema !== null) {
        $result = \odbc_tables($odbc, $catalog, $schema);
    } elseif ($catalog !== null) {
        $result = \odbc_tables($odbc, $catalog);
    } else {
        $result = \odbc_tables($odbc);
    }
    if ($result === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $result;
}
