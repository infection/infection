<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\UodbcException;
function odbc_autocommit($odbc, bool $enable = \false)
{
    \error_clear_last();
    $safeResult = \odbc_autocommit($odbc, $enable);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $safeResult;
}
function odbc_binmode(int $statement, int $mode) : void
{
    \error_clear_last();
    $safeResult = \odbc_binmode($statement, $mode);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
}
function odbc_columnprivileges($odbc, string $catalog, string $schema, string $table, string $column)
{
    \error_clear_last();
    $safeResult = \odbc_columnprivileges($odbc, $catalog, $schema, $table, $column);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $safeResult;
}
function odbc_columns($odbc, string $catalog = null, string $schema = null, string $table = null, string $column = null)
{
    \error_clear_last();
    if ($column !== null) {
        $safeResult = \odbc_columns($odbc, $catalog, $schema, $table, $column);
    } elseif ($table !== null) {
        $safeResult = \odbc_columns($odbc, $catalog, $schema, $table);
    } elseif ($schema !== null) {
        $safeResult = \odbc_columns($odbc, $catalog, $schema);
    } elseif ($catalog !== null) {
        $safeResult = \odbc_columns($odbc, $catalog);
    } else {
        $safeResult = \odbc_columns($odbc);
    }
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $safeResult;
}
function odbc_commit($odbc) : void
{
    \error_clear_last();
    $safeResult = \odbc_commit($odbc);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
}
function odbc_connect(string $dsn, string $user, string $password, int $cursor_option = \SQL_CUR_USE_DRIVER)
{
    \error_clear_last();
    $safeResult = \odbc_connect($dsn, $user, $password, $cursor_option);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $safeResult;
}
function odbc_cursor($statement) : string
{
    \error_clear_last();
    $safeResult = \odbc_cursor($statement);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $safeResult;
}
function odbc_data_source($odbc, int $fetch_type) : array
{
    \error_clear_last();
    $safeResult = \odbc_data_source($odbc, $fetch_type);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $safeResult;
}
function odbc_exec($odbc, string $query)
{
    \error_clear_last();
    $safeResult = \odbc_exec($odbc, $query);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $safeResult;
}
function odbc_execute($statement, array $params = []) : void
{
    \error_clear_last();
    $safeResult = \odbc_execute($statement, $params);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
}
function odbc_fetch_into($statement, ?array &$array, int $row = 0) : int
{
    \error_clear_last();
    $safeResult = \odbc_fetch_into($statement, $array, $row);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $safeResult;
}
function odbc_field_len($statement, int $field) : int
{
    \error_clear_last();
    $safeResult = \odbc_field_len($statement, $field);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $safeResult;
}
function odbc_field_name($statement, int $field) : string
{
    \error_clear_last();
    $safeResult = \odbc_field_name($statement, $field);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $safeResult;
}
function odbc_field_num($statement, string $field) : int
{
    \error_clear_last();
    $safeResult = \odbc_field_num($statement, $field);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $safeResult;
}
function odbc_field_scale($statement, int $field) : int
{
    \error_clear_last();
    $safeResult = \odbc_field_scale($statement, $field);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $safeResult;
}
function odbc_field_type($statement, int $field) : string
{
    \error_clear_last();
    $safeResult = \odbc_field_type($statement, $field);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $safeResult;
}
function odbc_foreignkeys($odbc, string $pk_catalog, string $pk_schema, string $pk_table, string $fk_catalog, string $fk_schema, string $fk_table)
{
    \error_clear_last();
    $safeResult = \odbc_foreignkeys($odbc, $pk_catalog, $pk_schema, $pk_table, $fk_catalog, $fk_schema, $fk_table);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $safeResult;
}
function odbc_gettypeinfo($odbc, int $data_type = 0)
{
    \error_clear_last();
    $safeResult = \odbc_gettypeinfo($odbc, $data_type);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $safeResult;
}
function odbc_longreadlen($statement, int $length) : void
{
    \error_clear_last();
    $safeResult = \odbc_longreadlen($statement, $length);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
}
function odbc_pconnect(string $dsn, string $user, string $password, int $cursor_option = \SQL_CUR_USE_DRIVER)
{
    \error_clear_last();
    $safeResult = \odbc_pconnect($dsn, $user, $password, $cursor_option);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $safeResult;
}
function odbc_prepare($odbc, string $query)
{
    \error_clear_last();
    $safeResult = \odbc_prepare($odbc, $query);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $safeResult;
}
function odbc_primarykeys($odbc, string $catalog, string $schema, string $table)
{
    \error_clear_last();
    $safeResult = \odbc_primarykeys($odbc, $catalog, $schema, $table);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $safeResult;
}
function odbc_procedurecolumns($odbc, string $catalog = null, string $schema = null, string $procedure = null, string $column = null)
{
    \error_clear_last();
    if ($column !== null) {
        $safeResult = \odbc_procedurecolumns($odbc, $catalog, $schema, $procedure, $column);
    } elseif ($procedure !== null) {
        $safeResult = \odbc_procedurecolumns($odbc, $catalog, $schema, $procedure);
    } elseif ($schema !== null) {
        $safeResult = \odbc_procedurecolumns($odbc, $catalog, $schema);
    } elseif ($catalog !== null) {
        $safeResult = \odbc_procedurecolumns($odbc, $catalog);
    } else {
        $safeResult = \odbc_procedurecolumns($odbc);
    }
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $safeResult;
}
function odbc_procedures($odbc, string $catalog = null, string $schema = null, string $procedure = null)
{
    \error_clear_last();
    if ($procedure !== null) {
        $safeResult = \odbc_procedures($odbc, $catalog, $schema, $procedure);
    } elseif ($schema !== null) {
        $safeResult = \odbc_procedures($odbc, $catalog, $schema);
    } elseif ($catalog !== null) {
        $safeResult = \odbc_procedures($odbc, $catalog);
    } else {
        $safeResult = \odbc_procedures($odbc);
    }
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $safeResult;
}
function odbc_result_all($statement, string $format = "") : int
{
    \error_clear_last();
    $safeResult = \odbc_result_all($statement, $format);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $safeResult;
}
function odbc_result($statement, $field)
{
    \error_clear_last();
    $safeResult = \odbc_result($statement, $field);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $safeResult;
}
function odbc_rollback($odbc) : void
{
    \error_clear_last();
    $safeResult = \odbc_rollback($odbc);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
}
function odbc_setoption($odbc, int $which, int $option, int $value) : void
{
    \error_clear_last();
    $safeResult = \odbc_setoption($odbc, $which, $option, $value);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
}
function odbc_specialcolumns($odbc, int $type, string $catalog, string $schema, string $table, int $scope, int $nullable)
{
    \error_clear_last();
    $safeResult = \odbc_specialcolumns($odbc, $type, $catalog, $schema, $table, $scope, $nullable);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $safeResult;
}
function odbc_statistics($odbc, string $catalog, string $schema, string $table, int $unique, int $accuracy)
{
    \error_clear_last();
    $safeResult = \odbc_statistics($odbc, $catalog, $schema, $table, $unique, $accuracy);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $safeResult;
}
function odbc_tableprivileges($odbc, string $catalog, string $schema, string $table)
{
    \error_clear_last();
    $safeResult = \odbc_tableprivileges($odbc, $catalog, $schema, $table);
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $safeResult;
}
function odbc_tables($odbc, string $catalog = null, string $schema = null, string $table = null, string $types = null)
{
    \error_clear_last();
    if ($types !== null) {
        $safeResult = \odbc_tables($odbc, $catalog, $schema, $table, $types);
    } elseif ($table !== null) {
        $safeResult = \odbc_tables($odbc, $catalog, $schema, $table);
    } elseif ($schema !== null) {
        $safeResult = \odbc_tables($odbc, $catalog, $schema);
    } elseif ($catalog !== null) {
        $safeResult = \odbc_tables($odbc, $catalog);
    } else {
        $safeResult = \odbc_tables($odbc);
    }
    if ($safeResult === \false) {
        throw UodbcException::createFromPhpError();
    }
    return $safeResult;
}
