<?php


use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;



















function odbc_autocommit($connection_id, $OnOff = false) {}





















function odbc_binmode($result_id, $mode) {}








function odbc_close($connection_id) {}






function odbc_close_all() {}







































function odbc_columns($connection_id, $qualifier = null, $schema = null, $table_name = null, $column_name = null) {}








function odbc_commit($connection_id) {}

























function odbc_connect($dsn, $user, $password, $cursor_type = null) {}









function odbc_cursor($result_id) {}














function odbc_data_source($connection_id, $fetch_type) {}



























function odbc_execute($result_id, array $parameters_array = null) {}














function odbc_error($connection_id = null) {}














function odbc_errormsg($connection_id = null) {}















function odbc_exec($connection_id, $query_string, #[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $flags = null) {}













function odbc_fetch_array($result, $rownumber = null) {}













function odbc_fetch_object($result, $rownumber = null) {}
























function odbc_fetch_row($result_id, $row_number = null) {}



















function odbc_fetch_into($result_id, array &$result_array, $rownumber = null) {}












function odbc_field_len($result_id, $field_number) {}












function odbc_field_scale($result_id, $field_number) {}












function odbc_field_name($result_id, $field_number) {}












function odbc_field_type($result_id, $field_number) {}













function odbc_field_num($result_id, $field_name) {}









function odbc_free_result($result_id) {}


































function odbc_gettypeinfo($connection_id, $data_type = null) {}














function odbc_longreadlen($result_id, $length) {}









function odbc_next_result($result_id) {}









function odbc_num_fields($result_id) {}










function odbc_num_rows($result_id) {}











function odbc_pconnect($dsn, $user, $password, $cursor_type = null) {}












function odbc_prepare($connection_id, $query_string) {}















function odbc_result($result_id, $field) {}













function odbc_result_all($result_id, $format = null) {}








function odbc_rollback($connection_id) {}






















function odbc_setoption($id, $function, $option, $param) {}










































function odbc_specialcolumns($connection_id, $type, $qualifier, $owner, $table, $scope, $nullable) {}







































function odbc_statistics($connection_id, $qualifier, $owner, $table_name, $unique, $accuracy) {}





































function odbc_tables($connection_id, $qualifier = null, $owner = null, $name = null, $types = null) {}




















function odbc_primarykeys($connection_id, $qualifier, $owner, $table) {}






































function odbc_columnprivileges($connection_id, $qualifier, $owner, $table_name, $column_name) {}





























function odbc_tableprivileges($connection_id, $qualifier, $owner, $name) {}


























































function odbc_foreignkeys($connection_id, $pk_qualifier, $pk_owner, $pk_table, $fk_qualifier, $fk_owner, $fk_table) {}





















function odbc_procedures($connection_id) {}



























function odbc_procedurecolumns($connection_id) {}








function odbc_do($connection_id, $query, $flags) {}







function odbc_field_precision($result_id, $field_number) {}

define('ODBC_TYPE', "unixODBC");
define('ODBC_BINMODE_PASSTHRU', 0);
define('ODBC_BINMODE_RETURN', 1);
define('ODBC_BINMODE_CONVERT', 2);
define('SQL_ODBC_CURSORS', 110);
define('SQL_CUR_USE_DRIVER', 2);
define('SQL_CUR_USE_IF_NEEDED', 0);
define('SQL_CUR_USE_ODBC', 1);
define('SQL_CONCURRENCY', 7);
define('SQL_CONCUR_READ_ONLY', 1);
define('SQL_CONCUR_LOCK', 2);
define('SQL_CONCUR_ROWVER', 3);
define('SQL_CONCUR_VALUES', 4);
define('SQL_CURSOR_TYPE', 6);
define('SQL_CURSOR_FORWARD_ONLY', 0);
define('SQL_CURSOR_KEYSET_DRIVEN', 1);
define('SQL_CURSOR_DYNAMIC', 2);
define('SQL_CURSOR_STATIC', 3);
define('SQL_KEYSET_SIZE', 8);
define('SQL_FETCH_FIRST', 2);
define('SQL_FETCH_NEXT', 1);
define('SQL_CHAR', 1);
define('SQL_VARCHAR', 12);
define('SQL_LONGVARCHAR', -1);
define('SQL_DECIMAL', 3);
define('SQL_NUMERIC', 2);
define('SQL_BIT', -7);
define('SQL_TINYINT', -6);
define('SQL_SMALLINT', 5);
define('SQL_INTEGER', 4);
define('SQL_BIGINT', -5);
define('SQL_REAL', 7);
define('SQL_FLOAT', 6);
define('SQL_DOUBLE', 8);
define('SQL_BINARY', -2);
define('SQL_VARBINARY', -3);
define('SQL_LONGVARBINARY', -4);
define('SQL_DATE', 9);
define('SQL_TIME', 10);
define('SQL_TIMESTAMP', 11);
define('SQL_TYPE_DATE', 91);
define('SQL_TYPE_TIME', 92);
define('SQL_TYPE_TIMESTAMP', 93);
define('SQL_WCHAR', -8);
define('SQL_WVARCHAR', -9);
define('SQL_WLONGVARCHAR', -10);
define('SQL_BEST_ROWID', 1);
define('SQL_ROWVER', 2);
define('SQL_SCOPE_CURROW', 0);
define('SQL_SCOPE_TRANSACTION', 1);
define('SQL_SCOPE_SESSION', 2);
define('SQL_NO_NULLS', 0);
define('SQL_NULLABLE', 1);
define('SQL_INDEX_UNIQUE', 0);
define('SQL_INDEX_ALL', 1);
define('SQL_ENSURE', 1);
define('SQL_QUICK', 0);


