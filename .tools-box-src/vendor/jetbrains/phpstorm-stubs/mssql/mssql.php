<?php



/**
@removed























*/
function mssql_connect($servername = null, $username = null, $password = null, $new_link = false) {}

/**
@removed
























*/
function mssql_pconnect($servername = null, $username = null, $password = null, $new_link = false) {}

/**
@removed












*/
function mssql_close($link_identifier = null) {}

/**
@removed
























*/
function mssql_select_db($database_name, $link_identifier = null) {}

/**
@removed





















*/
function mssql_query($query, $link_identifier = null, $batch_size = 0) {}

/**
@removed








*/
function mssql_fetch_batch($result) {}

/**
@removed









*/
function mssql_rows_affected($link_identifier) {}

/**
@removed








*/
function mssql_free_result($result) {}

/**
@removed





*/
function mssql_get_last_message() {}

/**
@removed








*/
function mssql_num_rows($result) {}

/**
@removed








*/
function mssql_num_fields($result) {}

/**
@removed













*/
function mssql_fetch_field($result, $field_offset = -1) {}

/**
@removed









*/
function mssql_fetch_row($result) {}

/**
@removed















*/
function mssql_fetch_array($result, $result_type = MSSQL_BOTH) {}

/**
@removed









*/
function mssql_fetch_assoc($result_id) {}

/**
@removed









*/
function mssql_fetch_object($result) {}

/**
@removed











*/
function mssql_field_length($result, $offset = null) {}

/**
@removed











*/
function mssql_field_name($result, $offset = -1) {}

/**
@removed











*/
function mssql_field_type($result, $offset = -1) {}

/**
@removed










*/
function mssql_data_seek($result_identifier, $row_number) {}

/**
@removed











*/
function mssql_field_seek($result, $field_offset) {}

/**
@removed























*/
function mssql_result($result, $row, $field) {}

/**
@removed









*/
function mssql_next_result($result_id) {}

/**
@removed







*/
function mssql_min_error_severity($severity) {}

/**
@removed







*/
function mssql_min_message_severity($severity) {}

/**
@removed














*/
function mssql_init($sp_name, $link_identifier = null) {}

/**
@removed










































*/
function mssql_bind($stmt, $param_name, &$var, $type, $is_output = false, $is_null = false, $maxlen = -1) {}

/**
@removed










*/
function mssql_execute($stmt, $skip_results = false) {}

/**
@removed







*/
function mssql_free_statement($stmt) {}

/**
@removed










*/
function mssql_guid_string($binary, $short_format = null) {}







define('MSSQL_ASSOC', 1);







define('MSSQL_NUM', 2);








define('MSSQL_BOTH', 3);







define('SQLTEXT', 35);







define('SQLVARCHAR', 39);







define('SQLCHAR', 47);





define('SQLINT1', 48);






define('SQLINT2', 52);






define('SQLINT4', 56);







define('SQLBIT', 50);





define('SQLFLT4', 59);





define('SQLFLT8', 62);
define('SQLFLTN', 109);


