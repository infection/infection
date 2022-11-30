<?php


use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;

/**
@removed


*/
class OCI_Lob
{






public function load() {}








public function tell() {}












public function truncate($length = 0) {}









public function erase($offset = null, $length = null) {}



















public function flush($flag = null) {}











public function setbuffering($on_off) {}








public function getbuffering() {}







public function rewind() {}










public function read($length) {}








public function eof() {}





















public function seek($offset, $whence = OCI_SEEK_SET) {}















public function write($data, $length = null) {}










public function append(#[LanguageLevelTypeAware(['8.0' => 'OCILob'], default: 'OCI_Lob')] $lob_from) {}








public function size() {}










public function writetofile($filename, $start, $length) {}
















public function export($filename, $start = null, $length = null) {}










public function import($filename) {}
















public function writeTemporary($data, $lob_type = OCI_TEMP_CLOB) {}







public function close() {}













public function save($data, $offset = null) {}








public function savefile($filename) {}







public function free() {}
}

/**
@removed


*/
class OCI_Collection
{









public function append($value) {}












public function getelem($index) {}













public function assignelem($index, $value) {}










public function assign(#[LanguageLevelTypeAware(['8.0' => 'OCICollection'], default: 'OCI_Collection')] $from) {}







public function size() {}










public function max() {}










public function trim($num) {}







public function free() {}
}
















function oci_register_taf_callback($connection, $callbackFn) {}










function oci_unregister_taf_callback($connection) {}
































function oci_define_by_name($statement, $column_name, &$variable, $type = SQLT_CHR) {}
















































function oci_bind_by_name($statement, $bv_name, &$variable, $maxlength = -1, $type = SQLT_CHR) {}































function oci_bind_array_by_name($statement, $name, array &$var_array, $max_table_length, $max_item_length = -1, $type = SQLT_AFC) {}













function oci_field_is_null($statement, $field) {}













function oci_field_name($statement, $field) {}














function oci_field_size($statement, $field) {}













function oci_field_scale($statement, $field) {}













function oci_field_precision($statement, $field) {}













function oci_field_type($statement, $field) {}













function oci_field_type_raw($statement, $field) {}



































































function oci_execute($statement, $mode = OCI_COMMIT_ON_SUCCESS) {}










function oci_cancel($statement) {}












function oci_fetch($statement) {}



































function oci_fetch_object($statement) {}












function oci_fetch_row($statement) {}












function oci_fetch_assoc($statement) {}






















































































function oci_fetch_array($statement, $mode = null) {}












#[Deprecated(since: "5.4")]
function ocifetchinto($statement_resource, &$result, $mode = null) {}

























































































function oci_fetch_all($statement, array &$output, $skip = 0, $maxrows = -1, $flags = OCI_FETCHSTATEMENT_BY_COLUMN|OCI_ASSOC) {}










function oci_free_statement($statement) {}

/**
@removed







*/
function oci_internal_debug($onoff) {}










function oci_num_fields($statement) {}




















function oci_parse($connection, $sql_text) {}

















function oci_get_implicit_resultset($statement) {}











function oci_new_cursor($connection) {}















function oci_result($statement, $field) {}







function oci_client_version() {}








function oci_server_version($connection) {}

































































function oci_statement_type($statement) {}










function oci_num_rows($statement) {}












function oci_close($connection) {}





























































































function oci_connect($username, $password, $connection_string = null, $character_set = null, $session_mode = null) {}




























































































function oci_new_connect($username, $password, $connection_string = null, $character_set = null, $session_mode = null) {}




























































































function oci_pconnect($username, $password, $connection_string = null, $character_set = null, $session_mode = null) {}
























































function oci_error($resource = null) {}








function oci_free_descriptor($descriptor) {}













function oci_lob_is_equal(
#[LanguageLevelTypeAware(['8.0' => 'OCILob'], default: 'OCI_Lob')] $lob1,
#[LanguageLevelTypeAware(['8.0' => 'OCILob'], default: 'OCI_Lob')] $lob2
) {}
















function oci_lob_copy(
#[LanguageLevelTypeAware(['8.0' => 'OCILob'], default: 'OCI_Lob')] $lob_to,
#[LanguageLevelTypeAware(['8.0' => 'OCILob'], default: 'OCI_Lob')] $lob_from,
$length = 0
) {}











function oci_commit($connection) {}












function oci_rollback($connection) {}
















#[LanguageLevelTypeAware(['8.0' => 'OCILob|false'], default: 'OCI_Lob|false')]
function oci_new_descriptor($connection, $type = OCI_DTYPE_LOB) {}














function oci_set_prefetch($statement, $rows) {}













function oci_set_client_identifier($connection, $client_identifier) {}











function oci_set_edition($edition) {}













function oci_set_module_name($connection, $module_name) {}













function oci_set_action($connection, $action_name) {}













function oci_set_client_info($connection, $client_info) {}




















function oci_password_change($connection, $username, $old_password, $new_password) {}



















#[LanguageLevelTypeAware(['8.0' => 'OCICollection|false'], default: 'OCI_Collection|false')]
function oci_new_collection($connection, $tdo, $schema = null) {}







function oci_free_cursor($statement_resource) {}









#[Deprecated(replacement: "oci_free_statement", since: "5.4")]
function ocifreecursor($statement_resource) {}












#[Deprecated(replacement: "oci_bind_by_name", since: "5.4")]
function ocibindbyname($statement, $column_name, &$variable, $maximum_length = -1, $type = SQLT_CHR) {}












#[Deprecated(replacement: "oci_define_by_name", since: "5.4")]
function ocidefinebyname($statement, $column_name, &$variable, $type = SQLT_CHR) {}









#[Deprecated(replacement: "oci_field_is_null", since: "5.4")]
function ocicolumnisnull($statement, $column_number_or_name) {}









#[Deprecated(replacement: "oci_field_name", since: "5.4")]
function ocicolumnname($statement, $column_number) {}









#[Deprecated(replacement: "oci_field_size", since: "5.4")]
function ocicolumnsize($statement, $column_number_or_name) {}









#[Deprecated(replacement: "oci_field_scale", since: "5.4")]
function ocicolumnscale($statement_resource, $column_number) {}









#[Deprecated(replacement: "oci_field_precision", since: "5.4")]
function ocicolumnprecision($statement_resource, $column_number) {}









#[Deprecated(replacement: "oci_field_type", since: "5.4")]
function ocicolumntype($statement_resource, $column_number) {}









#[Deprecated(replacement: "oci_field_type_raw", since: "5.4")]
function ocicolumntyperaw($statement_resource, $column_number) {}









#[Deprecated(replacement: "oci_execute", since: "5.4")]
function ociexecute($statement_resource, $mode = OCI_COMMIT_ON_SUCCESS) {}








#[Deprecated(replacement: 'oci_cancel', since: "5.4")]
function ocicancel($statement_resource) {}








#[Deprecated(replacement: "oci_fetch", since: "5.4")]
function ocifetch($statement_resource) {}












#[Deprecated(replacement: "oci_fetch_all", since: "5.4")]
function ocifetchstatement($statement_resource, &$output, $skip, $maximum_rows, $flags) {}








#[Deprecated(replacement: "oci_free_statement", since: "5.4")]
function ocifreestatement($statement_resource) {}

/**
@removed




*/
#[Deprecated(replacement: "oci_internal_debug", since: "5.4")]
function ociinternaldebug($mode) {}








#[Deprecated(replacement: "oci_num_fields", since: "5.4")]
function ocinumcols($statement_resource) {}









#[Deprecated(replacement: "oci_parse", since: "5.4")]
function ociparse($connection_resource, $sql_text) {}








#[Deprecated(replacement: "oci_new_cursor", since: "5.4")]
function ocinewcursor($connection_resource) {}









#[Deprecated(replacement: "oci_result", since: "5.4")]
function ociresult($statement_resource, $column_number_or_name) {}








#[Deprecated(replacement: "oci_server_version", since: "5.4")]
function ociserverversion($connection_resource) {}








#[Deprecated(replacement: "oci_statement_type", since: "5.4")]
function ocistatementtype($statement_resource) {}








#[Deprecated(replacement: "oci_num_rows", since: "5.4")]
function ocirowcount($statement_resource) {}








#[Deprecated(replacement: "oci_close", since: "5.4")]
function ocilogoff($connection_resource) {}












#[Deprecated(replacement: "oci_connect", since: "5.4")]
function ocilogon($username, $password, $connection_string, $character_set, $session_mode) {}













#[Deprecated(replacement: "oci_new_connect", since: "5.4")]
function ocinlogon($username, $password, $connection_string, $character_set, $session_mode) {}













#[Deprecated(replacement: "oci_pconnect", since: "5.4")]
function ociplogon($username, $password, $connection_string, $character_set, $session_mode) {}










#[Deprecated(replacement: "oci_error", since: "5.4")]
function ocierror($connection_or_statement_resource) {}









#[Deprecated(replacement: "OCI-Lob::free", since: "5.4")]
function ocifreedesc($lob_descriptor) {}











#[Deprecated(replacement: "OCI-Lob::save", since: "5.4")]
function ocisavelob(#[LanguageLevelTypeAware(['8.0' => 'OCILob'], default: 'OCI_Lob')] $lob_descriptor, $data, $offset) {}










#[Deprecated(replacement: "OCI_Lob::import", since: "5.4")]
function ocisavelobfile(#[LanguageLevelTypeAware(['8.0' => 'OCILob'], default: 'OCI_Lob')] $lob_descriptor, $filename) {}












#[Deprecated(replacement: "OCI_Lob::export", since: "5.4")]
function ociwritelobtofile(
#[LanguageLevelTypeAware(['8.0' => 'OCILob'], default: 'OCI_Lob')] $lob_descriptor,
$filename,
$start,
$length
) {}









#[Deprecated(replacement: "OCI_Lob::load", since: "5.4")]
function ociloadlob(#[LanguageLevelTypeAware(['8.0' => 'OCILob'], default: 'OCI_Lob')] $lob_descriptor) {}














#[Deprecated(replacement: "oci_commit", since: "5.4")]
function ocicommit($connection_resource) {}









#[Deprecated(replacement: "oci_rollback", since: "5.4")]
function ocirollback($connection_resource) {}













#[Deprecated(replacement: "oci_new_descriptor", since: "5.4")]
#[LanguageLevelTypeAware(['8.0' => 'OCILob|false'], default: 'OCI_Lob|false')]
function ocinewdescriptor($connection_resource, $type = OCI_DTYPE_LOB) {}













#[Deprecated(replacement: "oci_set_prefetch", since: "5.4")]
function ocisetprefetch($statement_resource, $number_of_rows) {}












function ocipasswordchange($connection_resource_or_connection_string_or_dbname, $username, $old_password, $new_password) {}








#[Deprecated(replacement: "OCI_Collection::free", since: "5.4")]
function ocifreecollection(#[LanguageLevelTypeAware(['8.0' => 'OCICollection'], default: 'OCI_Collection')] $collection) {}















#[Deprecated(replacement: "oci_new_collection", since: "5.4")]
#[LanguageLevelTypeAware(['8.0' => 'OCICollection|false'], default: 'OCI_Collection|false')]
function ocinewcollection($connection_resource, $tdo, $schema = null) {}










#[Deprecated(replacement: "OCI_Collection::append", since: "5.4")]
function ocicollappend(#[LanguageLevelTypeAware(['8.0' => 'OCICollection'], default: 'OCI_Collection')] $collection, $value) {}










#[Deprecated(replacement: "OCI_COLLection::getElem", since: "5.4")]
function ocicollgetelem(#[LanguageLevelTypeAware(['8.0' => 'OCICollection'], default: 'OCI_Collection')] $collection, $index) {}










#[Deprecated(replacement: "OCI_Collection::assignElem", since: "5.4")]
function ocicollassignelem(#[LanguageLevelTypeAware(['8.0' => 'OCICollection'], default: 'OCI_Collection')] $collection, $index, $value) {}









#[Deprecated(replacement: "OCI_COLLection::size", since: "5.4")]
function ocicollsize(#[LanguageLevelTypeAware(['8.0' => 'OCICollection'], default: 'OCI_Collection')] $collection) {}










#[Deprecated(replacement: "OCI_COLLection::max", since: "5.4")]
function ocicollmax(#[LanguageLevelTypeAware(['8.0' => 'OCICollection'], default: 'OCI_Collection')] $collection) {}










#[Deprecated(replacement: "OCI_Collection::trim", since: "5.4")]
function ocicolltrim(#[LanguageLevelTypeAware(['8.0' => 'OCICollection'], default: 'OCI_Collection')] $collection, $number) {}





















#[Deprecated(replacement: "OCI-Lob::writeTemporary", since: "5.4")]
function ociwritetemporarylob(
#[LanguageLevelTypeAware(['8.0' => 'OCILob'], default: 'OCI_Lob')] $lob_descriptor,
$data,
$lob_type = OCI_TEMP_CLOB
) {}








#[Deprecated(replacement: "OCI-Lob::close()", since: "5.4")]
function ocicloselob(#[LanguageLevelTypeAware(['8.0' => 'OCILob'], default: 'OCI_Lob')] $lob_descriptor) {}










#[Deprecated(replacement: "OCI-Collection::assign", since: "5.4")]
function ocicollassign(
#[LanguageLevelTypeAware(['8.0' => 'OCICollection'], default: 'OCI_Collection')] $to,
#[LanguageLevelTypeAware(['8.0' => 'OCICollection'], default: 'OCI_Collection')] $from
) {}




define('OCI_DEFAULT', 0);








define('OCI_SYSOPER', 4);








define('OCI_SYSDBA', 2);







define('OCI_CRED_EXT', -2147483648);








define('OCI_DESCRIBE_ONLY', 16);







define('OCI_COMMIT_ON_SUCCESS', 32);










define('OCI_NO_AUTO_COMMIT', 0);










define('OCI_EXACT_FETCH', 2);





define('OCI_SEEK_SET', 0);





define('OCI_SEEK_CUR', 1);





define('OCI_SEEK_END', 2);






define('OCI_LOB_BUFFER_FREE', 1);





define('SQLT_BFILEE', 114);





define('SQLT_CFILEE', 115);





define('SQLT_CLOB', 112);





define('SQLT_BLOB', 113);





define('SQLT_RDD', 104);





define('SQLT_INT', 3);





define('SQLT_NUM', 2);





define('SQLT_RSET', 116);






define('SQLT_AFC', 96);







define('SQLT_CHR', 1);






define('SQLT_VCS', 9);






define('SQLT_AVC', 97);






define('SQLT_STR', 5);






define('SQLT_LVC', 94);






define('SQLT_FLT', 4);





define('SQLT_UIN', 68);





define('SQLT_LNG', 8);





define('SQLT_LBI', 24);





define('SQLT_BIN', 23);






define('SQLT_ODT', 156);





define('SQLT_BDOUBLE', 22);





define('SQLT_BFLOAT', 21);







define('OCI_B_NTY', 108);





define('SQLT_NTY', 108);





define('OCI_SYSDATE', "SYSDATE");






define('OCI_B_BFILE', 114);






define('OCI_B_CFILEE', 115);






define('OCI_B_CLOB', 112);






define('OCI_B_BLOB', 113);






define('OCI_B_ROWID', 104);







define('OCI_B_CURSOR', 116);





define('OCI_B_BIN', 23);






define('OCI_B_INT', 3);






define('OCI_B_NUM', 2);





define('OCI_FETCHSTATEMENT_BY_COLUMN', 16);





define('OCI_FETCHSTATEMENT_BY_ROW', 32);







define('OCI_ASSOC', 1);







define('OCI_NUM', 2);







define('OCI_BOTH', 3);






define('OCI_RETURN_NULLS', 4);






define('OCI_RETURN_LOBS', 8);






define('OCI_DTYPE_FILE', 56);






define('OCI_DTYPE_LOB', 50);






define('OCI_DTYPE_ROWID', 54);





define('OCI_D_FILE', 56);





define('OCI_D_LOB', 50);





define('OCI_D_ROWID', 54);






define('OCI_TEMP_CLOB', 2);






define('OCI_TEMP_BLOB', 1);






define('SQLT_BOL', 252);







define('OCI_B_BOL', 252);


