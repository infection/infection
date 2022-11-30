<?php



use JetBrains\PhpStorm\Deprecated;

/**
@removed












































*/
#[Deprecated(since: '5.5')]
function mysql_connect($server = 'ini_get("mysql.default_host")', $username = 'ini_get("mysql.default_user")', $password = 'ini_get("mysql.default_password")', $new_link = false, $client_flags = 0) {}

/**
@removed






























*/
#[Deprecated(since: '5.5')]
function mysql_pconnect($server = 'ini_get("mysql.default_host")', $username = 'ini_get("mysql.default_user")', $password = 'ini_get("mysql.default_password")', $client_flags = null) {}

/**
@removed




*/
#[Deprecated(since: '5.5')]
function mysql_close($link_identifier = null) {}

/**
@removed







*/
#[Deprecated(since: '5.5')]
function mysql_select_db($database_name, $link_identifier = null) {}

/**
@removed




































*/
#[Deprecated(since: '5.5')]
function mysql_query($query, $link_identifier = null) {}

/**
@removed



















*/
#[Deprecated(since: '5.5')]
function mysql_unbuffered_query($query, $link_identifier = null) {}

/**
@removed


















*/
#[Deprecated('Use mysql_select_db() and mysql_query() instead', since: '5.3')]
function mysql_db_query($database, $query, $link_identifier = null) {}

/**
@removed







*/
#[Deprecated(since: '5.4')]
function mysql_list_dbs($link_identifier = null) {}

/**
@removed












*/
#[Deprecated(since: '5.3')]
function mysql_list_tables($database, $link_identifier = null) {}

/**
@removed

















*/
#[Deprecated(since: '5.5')]
function mysql_list_fields($database_name, $table_name, $link_identifier = null) {}

/**
@removed




*/
#[Deprecated(since: '5.5')]
function mysql_list_processes($link_identifier = null) {}

/**
@removed





*/
#[Deprecated(since: '5.5')]
function mysql_error($link_identifier = null) {}

/**
@removed





*/
#[Deprecated(since: '5.5')]
function mysql_errno($link_identifier = null) {}

/**
@removed






















*/
#[Deprecated(since: '5.5')]
function mysql_affected_rows($link_identifier = null) {}

/**
@removed







*/
#[Deprecated(since: '5.5')]
function mysql_insert_id($link_identifier = null) {}

/**
@removed


















*/
#[Deprecated(since: '5.5')]
function mysql_result($result, $row, $field = 0) {}

/**
@removed




*/
#[Deprecated(since: '5.5')]
function mysql_num_rows($result) {}

/**
@removed





*/
#[Deprecated(since: '5.5')]
function mysql_num_fields($result) {}

/**
@removed











*/
#[Deprecated(since: '5.5')]
function mysql_fetch_row($result) {}

/**
@removed
























*/
#[Deprecated(since: '5.5')]
function mysql_fetch_array($result, $result_type = MYSQL_BOTH) {}

/**
@removed














*/
#[Deprecated(since: '5.5')]
function mysql_fetch_assoc($result) {}

/**
@template
@removed




















*/
#[Deprecated(since: '5.5')]
function mysql_fetch_object($result, $class_name = 'stdClass', array $params = null) {}

/**
@removed







*/
#[Deprecated(since: '5.5')]
function mysql_data_seek($result, $row_number) {}

/**
@removed




*/
#[Deprecated(since: '5.5')]
function mysql_fetch_lengths($result) {}

/**
@removed

























*/
#[Deprecated(since: '5.5')]
function mysql_fetch_field($result, $field_offset = 0) {}

/**
@removed





*/
#[Deprecated(since: '5.5')]
function mysql_field_seek($result, $field_offset) {}

/**
@removed










*/
#[Deprecated(since: '5.5')]
function mysql_free_result($result) {}

/**
@removed





*/
#[Deprecated(since: '5.5')]
function mysql_field_name($result, $field_offset) {}

/**
@removed





*/
#[Deprecated(since: '5.5')]
function mysql_field_table($result, $field_offset) {}

/**
@removed





*/
#[Deprecated(since: '5.5')]
function mysql_field_len($result, $field_offset) {}

/**
@removed









*/
#[Deprecated(since: '5.5')]
function mysql_field_type($result, $field_offset) {}

/**
@removed














*/
#[Deprecated(since: '5.5')]
function mysql_field_flags($result, $field_offset) {}

/**
@removed






*/
#[Deprecated(replacement: 'mysql_real_escape_string(%parameter0%)', since: '5.3')]
function mysql_escape_string($unescaped_string) {}

/**
@removed







*/
#[Deprecated(since: '5.5')]
function mysql_real_escape_string($unescaped_string, $link_identifier = null) {}

/**
@removed







*/
#[Deprecated(since: '5.5')]
function mysql_stat($link_identifier = null) {}

/**
@removed




*/
#[Deprecated(since: '5.5')]
function mysql_thread_id($link_identifier = null) {}

/**
@removed




*/
#[Deprecated(since: '5.5')]
function mysql_client_encoding($link_identifier = null) {}

/**
@removed





*/
#[Deprecated(since: '5.5')]
function mysql_ping($link_identifier = null) {}

/**
@removed



*/
#[Deprecated(since: '5.5')]
function mysql_get_client_info() {}

/**
@removed





*/
#[Deprecated(since: '5.5')]
function mysql_get_host_info($link_identifier = null) {}

/**
@removed




*/
#[Deprecated(since: '5.5')]
function mysql_get_proto_info($link_identifier = null) {}

/**
@removed




*/
#[Deprecated(since: '5.5')]
function mysql_get_server_info($link_identifier = null) {}

/**
@removed







*/
#[Deprecated(since: '5.5')]
function mysql_info($link_identifier = null) {}

/**
@removed









*/
#[Deprecated(replacement: 'Use mysqli_set_charset instead', since: '5.5')]
function mysql_set_charset($charset, $link_identifier = null) {}

/**
@removed



*/
#[Deprecated(replacement: "mysql_db_query(%parametersList%)", since: '5.3')]
function mysql($database_name, $query, $link_identifier) {}

/**
@removed


*/
#[Deprecated(replacement: 'mysql_field_name(%parametersList%)', since: '5.5')]
function mysql_fieldname($result, $field_index) {}

/**
@removed


*/
#[Deprecated(replacement: 'mysql_field_table(%parametersList%)', since: '5.5')]
function mysql_fieldtable($result, $field_offset) {}

/**
@removed


*/
#[Deprecated(replacement: 'mysql_field_len(%parametersList%)', since: '5.5')]
function mysql_fieldlen($result, $field_offset) {}

/**
@removed


*/
#[Deprecated(replacement: 'mysql_field_type(%parametersList%)', since: '5.5')]
function mysql_fieldtype($result, $field_offset) {}

/**
@removed


*/
#[Deprecated(replacement: 'mysql_field_flags(%parametersList%)', since: '5.5')]
function mysql_fieldflags($result, $field_offset) {}

/**
@removed


*/
#[Deprecated(replacement: 'mysql_select_db(%parametersList%)', since: '5.5')]
function mysql_selectdb($database_name, $link_identifier) {}

/**
@removed

*/
#[Deprecated(replacement: 'mysql_free_result(%parametersList%)', since: '5.5')]
function mysql_freeresult($result) {}

/**
@removed

*/
#[Deprecated(replacement: 'mysql_num_fields(%parametersList%)', since: '5.5')]
function mysql_numfields($result) {}

/**
@removed





*/
#[Deprecated(replacement: 'mysql_num_rows(%parametersList%)', since: '5.5')]
function mysql_numrows($result) {}

/**
@removed

*/
#[Deprecated(replacement: 'mysql_list_dbs(%parametersList%)', since: '5.5')]
function mysql_listdbs($link_identifier) {}

/**
@removed


*/
#[Deprecated(replacement: 'mysql_list_tables(%parametersList%)', since: '5.5')]
function mysql_listtables($database_name, $link_identifier) {}

/**
@removed



*/
#[Deprecated(replacement: 'mysql_list_fields(%parametersList%)', since: '5.5')]
function mysql_listfields($database_name, $table_name, $link_identifier) {}

/**
@removed














*/
#[Deprecated(since: '5.5')]
function mysql_db_name($result, $row, $field = null) {}

/**
@removed



*/
#[Deprecated(replacement: 'mysql_db_name(%parametersList%)', since: '5.5')]
function mysql_dbname($result, $row, $field) {}

/**
@removed















*/
#[Deprecated(since: '5.5')]
function mysql_tablename($result, $i) {}

/**
@removed



*/
#[Deprecated(since: '5.5')]
function mysql_table_name($result, $row, $field) {}

/**
@removed




*/
define('MYSQL_ASSOC', 1);

/**
@removed




*/
define('MYSQL_NUM', 2);

/**
@removed




*/
define('MYSQL_BOTH', 3);

/**
@removed



*/
define('MYSQL_CLIENT_COMPRESS', 32);

/**
@removed





*/
define('MYSQL_CLIENT_SSL', 2048);

/**
@removed




*/
define('MYSQL_CLIENT_INTERACTIVE', 1024);

/**
@removed



*/
define('MYSQL_CLIENT_IGNORE_SPACE', 256);


