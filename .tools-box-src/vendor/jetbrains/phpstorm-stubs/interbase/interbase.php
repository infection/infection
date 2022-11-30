<?php



/**
@removed






































*/
function ibase_connect($database = null, $username = null, $password = null, $charset = null, $buffers = null, $dialect = null, $role = null, $sync = null) {}

/**
@removed






































*/
function ibase_pconnect($database = null, $username = null, $password = null, $charset = null, $buffers = null, $dialect = null, $role = null, $sync = null) {}

/**
@removed








*/
function ibase_close($connection_id = null) {}

/**
@removed







*/
function ibase_drop_db($connection = null) {}

/**
@removed





















*/
function ibase_query($link_identifier = null, $query, $bind_args = null) {}

/**
@removed
















*/
function ibase_fetch_row($result_identifier, $fetch_flag = null) {}

/**
@removed
















*/
function ibase_fetch_assoc($result, $fetch_flag = null) {}

/**
@removed
















*/
function ibase_fetch_object($result_id, $fetch_flag = null) {}

/**
@removed







*/
function ibase_free_result($result_identifier) {}

/**
@removed









*/
function ibase_name_result($result, $name) {}

/**
@removed






*/
function ibase_prepare($query) {}

/**
@removed

















*/
function ibase_execute($query, ...$bind_arg) {}

/**
@removed






*/
function ibase_free_query($query) {}

/**
@removed






*/
function ibase_gen_id($generator, $increment = null, $link_identifier = null) {}

/**
@removed






*/
function ibase_num_fields($result_id) {}

/**
@removed






*/
function ibase_num_params($query) {}

/**
@removed







*/
function ibase_affected_rows($link_identifier = null) {}

/**
@removed











*/
function ibase_field_info($result, $field_number) {}

/**
@removed











*/
function ibase_param_info($query, $param_number) {}

/**
@removed



















*/
function ibase_trans($trans_args = null, $link_identifier = null) {}

/**
@removed










*/
function ibase_commit($link_or_trans_identifier = null) {}

/**
@removed










*/
function ibase_rollback($link_or_trans_identifier = null) {}

/**
@removed












*/
function ibase_commit_ret($link_or_trans_identifier = null) {}

/**
@removed












*/
function ibase_rollback_ret($link_or_trans_identifier = null) {}

/**
@removed












*/
function ibase_blob_info($link_identifier, $blob_id) {}

/**
@removed








*/
function ibase_blob_create($link_identifier = null) {}

/**
@removed









*/
function ibase_blob_add($blob_handle, $data) {}

/**
@removed






*/
function ibase_blob_cancel($blob_handle) {}

/**
@removed










*/
function ibase_blob_close($blob_handle) {}

/**
@removed











*/
function ibase_blob_open($link_identifier, $blob_id) {}

/**
@removed










*/
function ibase_blob_get($blob_handle, $len) {}

/**
@removed





*/
function ibase_blob_echo($blob_id) {}

/**
@removed










*/
function ibase_blob_import($link_identifier, $file_handle) {}

/**
@removed



*/
function ibase_errmsg() {}

/**
@removed



*/
function ibase_errcode() {}

/**
@removed









*/
function ibase_add_user($service_handle, $user_name, $password, $first_name = null, $middle_name = null, $last_name = null) {}

/**
@removed









*/
function ibase_modify_user($service_handle, $user_name, $password, $first_name = null, $middle_name = null, $last_name = null) {}

/**
@removed





*/
function ibase_delete_user($service_handle, $user_name) {}

/**
@removed






*/
function ibase_service_attach($host, $dba_username, $dba_password) {}

/**
@removed




*/
function ibase_service_detach($service_handle) {}

/**
@removed








*/
function ibase_backup($service_handle, $source_db, $dest_file, $options = null, $verbose = null) {}

/**
@removed








*/
function ibase_restore($service_handle, $source_file, $dest_db, $options = null, $verbose = null) {}

/**
@removed







*/
function ibase_maintain_db($service_handle, $db, $action, $argument = null) {}

/**
@removed







*/
function ibase_db_info($service_handle, $db, $action, $argument = null) {}

/**
@removed





*/
function ibase_server_info($service_handle, $action) {}

/**
@removed









*/
function ibase_wait_event($event_name1, $event_name2 = null, ...$_) {}

/**
@removed





















*/
function ibase_set_event_handler($event_handler, $event_name1, $event_name2 = null, ...$_) {}

/**
@removed







*/
function ibase_free_event_handler($event) {}










































function fbird_connect($database = null, $username = null, $password = null, $charset = null, $buffers = null, $dialect = null, $role = null, $sync = null) {}










































function fbird_pconnect($database = null, $username = null, $password = null, $charset = null, $buffers = null, $dialect = null, $role = null, $sync = null) {}












function fbird_close($connection_id = null) {}











function fbird_drop_db($connection = null) {}

























function fbird_query($link_identifier = null, $query, $bind_args = null) {}




















function fbird_fetch_row($result_identifier, $fetch_flag = null) {}




















function fbird_fetch_assoc($result, $fetch_flag = null) {}




















function fbird_fetch_object($result_id, $fetch_flag = null) {}











function fbird_free_result($result_identifier) {}













function fbird_name_result($result, $name) {}










function fbird_prepare($query) {}




















function fbird_execute($query, ...$bind_arg) {}










function fbird_free_query($query) {}










function fbird_gen_id($generator, $increment = null, $link_identifier = null) {}










function fbird_num_fields($result_id) {}










function fbird_num_params($query) {}











function fbird_affected_rows($link_identifier = null) {}















function fbird_field_info($result, $field_number) {}















function fbird_param_info($query, $param_number) {}























function fbird_trans($trans_args = null, $link_identifier = null) {}














function fbird_commit($link_or_trans_identifier = null) {}














function fbird_rollback($link_or_trans_identifier = null) {}
















function fbird_commit_ret($link_or_trans_identifier = null) {}
















function fbird_rollback_ret($link_or_trans_identifier = null) {}
















function fbird_blob_info($link_identifier, $blob_id) {}












function fbird_blob_create($link_identifier = null) {}













function fbird_blob_add($blob_handle, $data) {}










function fbird_blob_cancel($blob_handle) {}














function fbird_blob_close($blob_handle) {}















function fbird_blob_open($link_identifier, $blob_id) {}














function fbird_blob_get($blob_handle, $len) {}









function fbird_blob_echo($blob_id) {}














function fbird_blob_import($link_identifier, $file_handle) {}







function fbird_errmsg() {}







function fbird_errcode() {}













function fbird_add_user($service_handle, $user_name, $password, $first_name = null, $middle_name = null, $last_name = null) {}













function fbird_modify_user($service_handle, $user_name, $password, $first_name = null, $middle_name = null, $last_name = null) {}









function fbird_delete_user($service_handle, $user_name) {}










function fbird_service_attach($host, $dba_username, $dba_password) {}








function fbird_service_detach($service_handle) {}












function fbird_backup($service_handle, $source_db, $dest_file, $options = null, $verbose = null) {}












function fbird_restore($service_handle, $source_file, $dest_db, $options = null, $verbose = null) {}











function fbird_maintain_db($service_handle, $db, $action, $argument = null) {}











function fbird_db_info($service_handle, $db, $action, $argument = null) {}









function fbird_server_info($service_handle, $action) {}













function fbird_wait_event($event_name1, $event_name2 = null, ...$_) {}

























function fbird_set_event_handler($event_handler, $event_name1, $event_name2 = null, ...$_) {}











function fbird_free_event_handler($event) {}

/**
@removed



*/
define('IBASE_DEFAULT', 0);



define('IBASE_CREATE', 0);




define('IBASE_TEXT', 1);
/**
@removed



*/
define('IBASE_FETCH_BLOBS', 1);
/**
@removed



*/
define('IBASE_FETCH_ARRAYS', 2);
/**
@removed



*/
define('IBASE_UNIXTIME', 4);
/**
@removed


*/
define('IBASE_WRITE', 1);
/**
@removed


*/
define('IBASE_READ', 2);
/**
@removed






*/
define('IBASE_COMMITTED', 8);
/**
@removed



*/
define('IBASE_CONSISTENCY', 16);
/**
@removed




*/
define('IBASE_CONCURRENCY', 4);




define('IBASE_REC_VERSION', 64);




define('IBASE_REC_NO_VERSION', 32);
/**
@removed


*/
define('IBASE_NOWAIT', 256);
/**
@removed


*/
define('IBASE_WAIT', 128);
/**
@removed
*/
define('IBASE_BKP_IGNORE_CHECKSUMS', 1);
/**
@removed
*/
define('IBASE_BKP_IGNORE_LIMBO', 2);
/**
@removed
*/
define('IBASE_BKP_METADATA_ONLY', 4);
/**
@removed
*/
define('IBASE_BKP_NO_GARBAGE_COLLECT', 8);
/**
@removed
*/
define('IBASE_BKP_OLD_DESCRIPTIONS', 16);
/**
@removed
*/
define('IBASE_BKP_NON_TRANSPORTABLE', 32);

/**
@removed


*/
define('IBASE_BKP_CONVERT', 64);
/**
@removed
*/
define('IBASE_RES_DEACTIVATE_IDX', 256);
/**
@removed
*/
define('IBASE_RES_NO_SHADOW', 512);
/**
@removed
*/
define('IBASE_RES_NO_VALIDITY', 1024);
/**
@removed
*/
define('IBASE_RES_ONE_AT_A_TIME', 2048);
/**
@removed
*/
define('IBASE_RES_REPLACE', 4096);
/**
@removed
*/
define('IBASE_RES_CREATE', 8192);

/**
@removed


*/
define('IBASE_RES_USE_ALL_SPACE', 16384);
/**
@removed
*/
define('IBASE_PRP_PAGE_BUFFERS', 5);
/**
@removed
*/
define('IBASE_PRP_SWEEP_INTERVAL', 6);
/**
@removed
*/
define('IBASE_PRP_SHUTDOWN_DB', 7);
/**
@removed
*/
define('IBASE_PRP_DENY_NEW_TRANSACTIONS', 10);
/**
@removed
*/
define('IBASE_PRP_DENY_NEW_ATTACHMENTS', 9);
/**
@removed
*/
define('IBASE_PRP_RESERVE_SPACE', 11);
/**
@removed
*/
define('IBASE_PRP_RES_USE_FULL', 35);
/**
@removed
*/
define('IBASE_PRP_RES', 36);
/**
@removed
*/
define('IBASE_PRP_WRITE_MODE', 12);
/**
@removed
*/
define('IBASE_PRP_WM_ASYNC', 37);
/**
@removed
*/
define('IBASE_PRP_WM_SYNC', 38);
/**
@removed
*/
define('IBASE_PRP_ACCESS_MODE', 13);
/**
@removed
*/
define('IBASE_PRP_AM_READONLY', 39);
/**
@removed
*/
define('IBASE_PRP_AM_READWRITE', 40);
/**
@removed
*/
define('IBASE_PRP_SET_SQL_DIALECT', 14);
/**
@removed
*/
define('IBASE_PRP_ACTIVATE', 256);
/**
@removed
*/
define('IBASE_PRP_DB_ONLINE', 512);
/**
@removed
*/
define('IBASE_RPR_CHECK_DB', 16);
/**
@removed
*/
define('IBASE_RPR_IGNORE_CHECKSUM', 32);
/**
@removed
*/
define('IBASE_RPR_KILL_SHADOWS', 64);
/**
@removed
*/
define('IBASE_RPR_MEND_DB', 4);
/**
@removed
*/
define('IBASE_RPR_VALIDATE_DB', 1);
/**
@removed
*/
define('IBASE_RPR_FULL', 128);

/**
@removed


*/
define('IBASE_RPR_SWEEP_DB', 2);
/**
@removed
*/
define('IBASE_STS_DATA_PAGES', 1);
/**
@removed
*/
define('IBASE_STS_DB_LOG', 2);
/**
@removed
*/
define('IBASE_STS_HDR_PAGES', 4);
/**
@removed
*/
define('IBASE_STS_IDX_PAGES', 8);

/**
@removed


*/
define('IBASE_STS_SYS_RELATIONS', 16);
/**
@removed
*/
define('IBASE_SVC_SERVER_VERSION', 55);
/**
@removed
*/
define('IBASE_SVC_IMPLEMENTATION', 56);
/**
@removed
*/
define('IBASE_SVC_GET_ENV', 59);
/**
@removed
*/
define('IBASE_SVC_GET_ENV_LOCK', 60);
/**
@removed
*/
define('IBASE_SVC_GET_ENV_MSG', 61);
/**
@removed
*/
define('IBASE_SVC_USER_DBPATH', 58);
/**
@removed
*/
define('IBASE_SVC_SVR_DB_INFO', 50);

/**
@removed


*/
define('IBASE_SVC_GET_USERS', 68);


