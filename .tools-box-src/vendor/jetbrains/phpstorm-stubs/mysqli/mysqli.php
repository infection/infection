<?php





use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Internal\TentativeType;




final class mysqli_sql_exception extends RuntimeException
{





#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
protected $sqlstate;






protected $code;




public function getSqlState(): string {}
}





final class mysqli_driver
{



#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $client_info;




#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $client_version;




#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $driver_version;




public $embedded;




#[LanguageLevelTypeAware(['8.1' => 'bool'], default: '')]
public $reconnect;




#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $report_mode;
}





class mysqli
{



#[LanguageLevelTypeAware(['8.1' => 'string|int'], default: '')]
public $affected_rows;




#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $client_info;




#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $client_version;




#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $connect_errno;




#[LanguageLevelTypeAware(['8.1' => 'string|null'], default: '')]
public $connect_error;




#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $errno;




#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $error;




#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $field_count;




#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $host_info;




#[LanguageLevelTypeAware(['8.1' => 'string|null'], default: '')]
public $info;




#[LanguageLevelTypeAware(['8.1' => 'int|string'], default: '')]
public $insert_id;




#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $server_info;




#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $server_version;




#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $sqlstate;




#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $protocol_version;




#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $thread_id;




#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $warning_count;





#[LanguageLevelTypeAware(['8.1' => 'array'], default: '')]
public $error_list;
public $stat;











public function __construct(
?string $hostname = null,
?string $username = null,
?string $password = null,
?string $database = null,
?int $port = null,
?string $socket = null
) {}









#[TentativeType]
public function autocommit(bool $enable): bool {}









#[TentativeType]
public function begin_transaction(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = 0,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $name = null
): bool {}




















#[TentativeType]
public function change_user(string $username, string $password, ?string $database): bool {}






#[TentativeType]
public function character_set_name(): string {}

/**
@removed
*/
#[Deprecated(since: '5.3')]
public function client_encoding() {}






public function close() {}








#[TentativeType]
public function commit(int $flags = 0, ?string $name = null): bool {}











#[TentativeType]
public function connect(
?string $hostname = null,
?string $username = null,
?string $password = null,
?string $database = null,
?int $port = null,
?string $socket = null
): bool {}






#[TentativeType]
public function dump_debug_info(): bool {}









public function debug(string $options) {}




















#[TentativeType]
public function get_charset(): ?object {}






#[TentativeType]
public function get_client_info(): string {}






#[TentativeType]
public function get_connection_stats(): array {}






#[TentativeType]
public function get_server_info(): string {}






#[TentativeType]
public function get_warnings(): mysqli_warning|false {}







public function init() {}







#[TentativeType]
public function kill(int $process_id): bool {}


















#[TentativeType]
public function multi_query(string $query): bool {}

/**
@removed








*/
public function mysqli($host = null, $username = null, $password = null, $database = null, $port = null, $socket = null) {}






#[TentativeType]
public function more_results(): bool {}






#[TentativeType]
public function next_result(): bool {}


















































#[TentativeType]
public function options(int $option, $value): bool {}






#[TentativeType]
public function ping(): bool {}






























#[TentativeType]
public function prepare(string $query): mysqli_stmt|false {}











































#[TentativeType]
public function query(
string $query,
#[PhpStormStubsElementAvailable(from: '7.1')] int $result_mode = MYSQLI_STORE_RESULT
): mysqli_result|bool {}













































































#[TentativeType]
public function real_connect(
?string $hostname = null,
?string $username = null,
?string $password = null,
?string $database = null,
?int $port = null,
?string $socket = null,
int $flags = null
): bool {}













#[TentativeType]
public function real_escape_string(string $string): string {}


















#[TentativeType]
public static function poll(?array &$read, ?array &$error, array &$reject, int $seconds, int $microseconds = 0): int|false {}






#[TentativeType]
public function reap_async_query(): mysqli_result|bool {}








#[TentativeType]
public function escape_string(string $string): string {}















#[TentativeType]
public function real_query(string $query): bool {}








#[TentativeType]
public function release_savepoint(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name): bool {}









#[TentativeType]
public function rollback(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = 0,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $name = null
): bool {}








#[TentativeType]
public function savepoint(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name): bool {}









#[TentativeType]
public function select_db(string $database): bool {}









#[TentativeType]
public function set_charset(string $charset): bool {}






#[TentativeType]
public function set_opt(int $option, $value): bool {}





















public function ssl_set(?string $key, ?string $certificate, ?string $ca_certificate, ?string $ca_path, ?string $cipher_algos) {}






#[TentativeType]
public function stat(): string|false {}






#[TentativeType]
public function stmt_init(): mysqli_stmt|false {}





















#[TentativeType]
public function store_result(int $mode = 0): mysqli_result|false {}






#[TentativeType]
public function thread_safe(): bool {}






#[TentativeType]
public function use_result(): mysqli_result|false {}







#[TentativeType]
public function refresh(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags): bool {}
}





final class mysqli_warning
{



#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $message;




#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $sqlstate;




#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $errno;





#[PhpStormStubsElementAvailable(from: '8.0')]
private function __construct() {}





#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')]
protected function __construct() {}






public function next(): bool {}
}






class mysqli_result implements IteratorAggregate
{



#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $current_field;




#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $field_count;




#[LanguageLevelTypeAware(['8.1' => 'array|null'], default: '')]
public $lengths;




#[LanguageLevelTypeAware(['8.1' => 'int|string'], default: '')]
public $num_rows;




#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $type;






public function __construct(
#[PhpStormStubsElementAvailable(from: '8.0')] mysqli $mysql,
#[PhpStormStubsElementAvailable(from: '8.0')] int $result_mode = MYSQLI_STORE_RESULT
) {}






#[TentativeType]
public function close(): void {}






#[TentativeType]
public function free(): void {}










#[TentativeType]
public function data_seek(int $offset): bool {}




































































#[TentativeType]
public function fetch_field(): object|false {}



























































#[TentativeType]
public function fetch_fields(): array {}

































































#[TentativeType]
public function fetch_field_direct(int $index): object|false {}












#[TentativeType]
public function fetch_all(#[PhpStormStubsElementAvailable(from: '7.0')] int $mode = MYSQLI_NUM): array {}





















#[TentativeType]
public function fetch_array(int $mode = MYSQLI_BOTH): array|false|null {}








#[TentativeType]
public function fetch_assoc(): array|false|null {}

/**
@template














*/
#[TentativeType]
public function fetch_object(string $class = 'stdClass', array $constructor_args = null): object|false|null {}








#[TentativeType]
public function fetch_row(): array|false|null {}











#[PhpStormStubsElementAvailable('8.1')]
public function fetch_column(int $column = 0): string|int|float|false|null {}










#[TentativeType]
public function field_seek(int $index): bool {}






#[TentativeType]
public function free_result(): void {}





public function getIterator(): Iterator {}
}





class mysqli_stmt
{



#[LanguageLevelTypeAware(['8.1' => 'int|string'], default: '')]
public $affected_rows;




#[LanguageLevelTypeAware(['8.1' => 'int|string'], default: '')]
public $insert_id;




#[LanguageLevelTypeAware(['8.1' => 'int|string'], default: '')]
public $num_rows;




#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $param_count;




#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $field_count;




#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $errno;




#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $error;




#[LanguageLevelTypeAware(['8.1' => 'array'], default: '')]
public $error_list;




#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $sqlstate;




#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
public $id;






public function __construct($mysql, $query) {}







#[TentativeType]
public function attr_get(int $attribute): int {}





















































#[TentativeType]
public function attr_set(int $attribute, int $value): bool {}






































public function bind_param($types, &$var1, &...$_) {}








public function bind_result(&$var1, &...$_) {}






public function close() {}










#[TentativeType]
public function data_seek(int $offset): void {}








#[TentativeType]
public function execute(#[PhpStormStubsElementAvailable('8.1')] ?array $params = null): bool {}






#[TentativeType]
public function fetch(): ?bool {}






#[TentativeType]
public function get_warnings(): mysqli_warning|false {}






#[TentativeType]
public function result_metadata(): mysqli_result|false {}






#[TentativeType]
public function more_results(): bool {}






#[TentativeType]
public function next_result(): bool {}






#[TentativeType]
public function num_rows(): string|int {}













#[TentativeType]
public function send_long_data(int $param_num, string $data): bool {}

/**
@removed

*/
#[Deprecated(since: '5.3')]
public function stmt() {}






#[TentativeType]
public function free_result(): void {}






#[TentativeType]
public function reset(): bool {}



























#[TentativeType]
public function prepare(string $query): bool {}






#[TentativeType]
public function store_result(): bool {}






#[TentativeType]
public function get_result(): mysqli_result|false {}
}










function mysqli_affected_rows(mysqli $mysql): string|int {}








function mysqli_autocommit(mysqli $mysql, bool $enable): bool {}










function mysqli_begin_transaction(mysqli $mysql, int $flags = 0, ?string $name): bool {}










function mysqli_change_user(mysqli $mysql, string $username, string $password, ?string $database): bool {}







function mysqli_character_set_name(mysqli $mysql): string {}







#[LanguageLevelTypeAware(['8.2' => 'true'], default: 'bool')]
function mysqli_close(mysqli $mysql): bool {}









function mysqli_commit(mysqli $mysql, int $flags = 0, ?string $name = null): bool {}













function mysqli_connect(?string $hostname = null, ?string $username = null, ?string $password = null, ?string $database = null, ?int $port = null, ?string $socket = null): mysqli|false {}






function mysqli_connect_errno(): int {}






function mysqli_connect_error(): ?string {}









function mysqli_data_seek(mysqli_result $result, int $offset): bool {}







function mysqli_dump_debug_info(mysqli $mysql): bool {}







#[LanguageLevelTypeAware(['8.2' => 'true'], default: 'bool')]
function mysqli_debug(string $options): bool {}







function mysqli_errno(mysqli $mysql): int {}








function mysqli_error_list(mysqli $mysql): array {}








function mysqli_stmt_error_list(mysqli_stmt $statement): array {}







function mysqli_error(mysqli $mysql): string {}









function mysqli_stmt_execute(mysqli_stmt $statement, #[PhpStormStubsElementAvailable('8.1')] ?array $params = null): bool {}










#[Deprecated(since: '5.3')]
function mysqli_execute(mysqli_stmt $statement, #[PhpStormStubsElementAvailable('8.1')] ?array $params = null): bool {}








function mysqli_execute_query(mysqli $mysql, string $query, ?array $params = null): mysqli_result|bool {}








function mysqli_fetch_field(mysqli_result $result): object|false {}








function mysqli_fetch_fields(mysqli_result $result): array {}









function mysqli_fetch_field_direct(mysqli_result $result, int $index): object|false {}








function mysqli_fetch_lengths(mysqli_result $result): array|false {}









function mysqli_fetch_all(
mysqli_result $result,
#[PhpStormStubsElementAvailable(from: '7.0')] int $mode = MYSQLI_NUM
): array {}










function mysqli_fetch_array(mysqli_result $result, int $mode = MYSQLI_BOTH): array|false|null {}










function mysqli_fetch_assoc(mysqli_result $result): array|null|false {}

/**
@template










*/
function mysqli_fetch_object(mysqli_result $result, string $class = 'stdClass', array $constructor_args = []): object|null|false {}










function mysqli_fetch_row(mysqli_result $result): array|false|null {}













#[PhpStormStubsElementAvailable('8.1')]
function mysqli_fetch_column(mysqli_result $result, int $column = 0): string|int|float|false|null {}







function mysqli_field_count(mysqli $mysql): int {}









function mysqli_field_seek(mysqli_result $result, int $index): bool {}








function mysqli_field_tell(mysqli_result $result): int {}








function mysqli_free_result(mysqli_result $result): void {}

/**
@removed





*/
function mysqli_get_cache_stats(mysqli $mysql) {}







function mysqli_get_connection_stats(mysqli $mysql): array {}






function mysqli_get_client_stats(): array {}







function mysqli_get_charset(mysqli $mysql): ?object {}







#[LanguageLevelTypeAware(['8.0' => 'string'], default: '?string')]
function mysqli_get_client_info(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.1')] mysqli $mysql,
#[PhpStormStubsElementAvailable(from: '8.0')] ?mysqli $mysql = null
) {}






function mysqli_get_client_version(#[PhpStormStubsElementAvailable(from: '5.3', to: '7.3')] $link): int {}







function mysqli_get_host_info(mysqli $mysql): string {}




































function mysqli_get_links_stats(): array {}







function mysqli_get_proto_info(mysqli $mysql): int {}







function mysqli_get_server_info(mysqli $mysql): string {}








function mysqli_get_server_version(mysqli $mysql): int {}







function mysqli_get_warnings(mysqli $mysql): mysqli_warning|false {}







function mysqli_init(): mysqli|false {}







function mysqli_info(mysqli $mysql): ?string {}








function mysqli_insert_id(mysqli $mysql): string|int {}









function mysqli_kill(mysqli $mysql, int $process_id): bool {}

/**
@removed




*/
function mysqli_set_local_infile_default(mysqli $mysql) {}

/**
@removed





*/
function mysqli_set_local_infile_handler(mysqli $mysql, callable $read_func): bool {}








function mysqli_more_results(mysqli $mysql): bool {}








function mysqli_multi_query(
mysqli $mysql,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.0')] string $query,
#[PhpStormStubsElementAvailable(from: '7.1', to: '7.4')] string $query = null,
#[PhpStormStubsElementAvailable(from: '8.0')] string $query
): bool {}







function mysqli_next_result(mysqli $mysql): bool {}








function mysqli_num_fields(mysqli_result $result): int {}








function mysqli_num_rows(mysqli_result $result): string|int {}









function mysqli_options(mysqli $mysql, int $option, $value): bool {}







function mysqli_ping(mysqli $mysql): bool {}











function mysqli_poll(?array &$read, ?array &$error, array &$reject, int $seconds, int $microseconds = 0): int|false {}









function mysqli_prepare(mysqli $mysql, string $query): mysqli_stmt|false {}






































function mysqli_report(int $flags): bool {}












function mysqli_query(
mysqli $mysql,
string $query,
#[PhpStormStubsElementAvailable(from: '7.1')] int $result_mode = MYSQLI_STORE_RESULT
): mysqli_result|bool {}















function mysqli_real_connect(mysqli $mysql, ?string $hostname, ?string $username, ?string $password, ?string $database, ?int $port, ?string $socket, int $flags = 0): bool {}








function mysqli_real_escape_string(mysqli $mysql, string $string): string {}








function mysqli_real_query(
mysqli $mysql,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.0')] string $query,
#[PhpStormStubsElementAvailable(from: '7.1', to: '7.4')] string $query = null,
#[PhpStormStubsElementAvailable(from: '8.0')] string $query
): bool {}









function mysqli_reap_async_query(mysqli $mysql): mysqli_result|bool {}









function mysqli_release_savepoint(mysqli $mysql, string $name): bool {}









function mysqli_rollback(mysqli $mysql, int $flags = 0, ?string $name): bool {}









function mysqli_savepoint(mysqli $mysql, string $name): bool {}








function mysqli_select_db(mysqli $mysql, string $database): bool {}








function mysqli_set_charset(mysqli $mysql, string $charset): bool {}







function mysqli_stmt_affected_rows(mysqli_stmt $statement): string|int {}








#[LanguageLevelTypeAware(["8.0" => "int"], default: "int|false")]
function mysqli_stmt_attr_get(mysqli_stmt $statement, int $attribute): false|int {}









function mysqli_stmt_attr_set(mysqli_stmt $statement, int $attribute, int $value): bool {}







function mysqli_stmt_field_count(mysqli_stmt $statement): int {}







function mysqli_stmt_init(mysqli $mysql): mysqli_stmt|false {}








function mysqli_stmt_prepare(mysqli_stmt $statement, string $query): bool {}







function mysqli_stmt_result_metadata(mysqli_stmt $statement): mysqli_result|false {}









function mysqli_stmt_send_long_data(mysqli_stmt $statement, int $param_num, string $data): bool {}







































function mysqli_stmt_bind_param(
mysqli_stmt $statement,
string $types,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] mixed &$vars,
mixed &...$vars
): bool {}








function mysqli_stmt_bind_result(
mysqli_stmt $statement,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] mixed &$vars,
mixed &...$vars
): bool {}







function mysqli_stmt_fetch(mysqli_stmt $statement): ?bool {}







function mysqli_stmt_free_result(mysqli_stmt $statement): void {}









function mysqli_stmt_get_result(mysqli_stmt $statement): mysqli_result|false {}







function mysqli_stmt_get_warnings(mysqli_stmt $statement): mysqli_warning|false {}







function mysqli_stmt_insert_id(mysqli_stmt $statement): string|int {}







function mysqli_stmt_reset(mysqli_stmt $statement): bool {}







function mysqli_stmt_param_count(mysqli_stmt $statement): int {}







function mysqli_sqlstate(mysqli $mysql): string {}







function mysqli_stat(mysqli $mysql): string|false {}












#[LanguageLevelTypeAware(['8.2' => 'true'], default: 'bool')]
function mysqli_ssl_set(
mysqli $mysql,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: 'string')] $key,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: 'string')] $certificate,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: 'string')] $ca_certificate,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: 'string')] $ca_path,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: 'string')] $cipher_algos
): bool {}







#[LanguageLevelTypeAware(['8.2' => 'true'], default: 'bool')]
function mysqli_stmt_close(mysqli_stmt $statement): bool {}








function mysqli_stmt_data_seek(mysqli_stmt $statement, int $offset): void {}







function mysqli_stmt_errno(mysqli_stmt $statement): int {}







function mysqli_stmt_error(mysqli_stmt $statement): string {}







function mysqli_stmt_more_results(mysqli_stmt $statement): bool {}







function mysqli_stmt_next_result(mysqli_stmt $statement): bool {}







function mysqli_stmt_num_rows(mysqli_stmt $statement): string|int {}







function mysqli_stmt_sqlstate(mysqli_stmt $statement): string {}







function mysqli_stmt_store_result(mysqli_stmt $statement): bool {}








function mysqli_store_result(mysqli $mysql, int $mode = 0): mysqli_result|false {}







function mysqli_thread_id(mysqli $mysql): int {}






function mysqli_thread_safe(): bool {}







function mysqli_use_result(mysqli $mysql): mysqli_result|false {}







function mysqli_warning_count(mysqli $mysql): int {}








function mysqli_refresh(mysqli $mysql, int $flags): bool {}

/**
@removed




*/
#[Deprecated(since: '5.3')]
function mysqli_bind_param(mysqli_stmt $statement, string $types) {}

/**
@removed





*/
#[Deprecated(since: '5.3')]
function mysqli_bind_result(mysqli_stmt $statement, string $types, mixed &$var1) {}

/**
@removed




*/
#[Deprecated(since: '5.3')]
function mysqli_client_encoding(mysqli $mysql): string {}








function mysqli_escape_string(
mysqli $mysql,
string $string,
#[PhpStormStubsElementAvailable(from: '7.1', to: '7.4')] $resultmode = null
): string {}

/**
@removed




*/
#[Deprecated(since: '5.3')]
function mysqli_fetch(mysqli_stmt $statement): bool {}

/**
@removed




*/
#[Deprecated(since: '5.3')]
function mysqli_param_count(mysqli_stmt $statement): int {}

/**
@removed




*/
#[Deprecated(since: '5.3')]
function mysqli_get_metadata(mysqli_stmt $statement): false|mysqli_result {}

/**
@removed






*/
#[Deprecated(since: '5.3')]
function mysqli_send_long_data(mysqli_stmt $statement, int $param_num, string $data): bool {}









function mysqli_set_opt(
#[PhpStormStubsElementAvailable(from: '8.0')] mysqli $mysql,
#[PhpStormStubsElementAvailable(from: '8.0')] int $option,
#[PhpStormStubsElementAvailable(from: '8.0')] $value
): bool {}








define('MYSQLI_READ_DEFAULT_GROUP', 5);







define('MYSQLI_READ_DEFAULT_FILE', 4);







define('MYSQLI_OPT_CONNECT_TIMEOUT', 0);







define('MYSQLI_OPT_LOCAL_INFILE', 8);







define('MYSQLI_SERVER_PUBLIC_KEY', 35);







define('MYSQLI_INIT_COMMAND', 3);
define('MYSQLI_OPT_NET_CMD_BUFFER_SIZE', 202);
define('MYSQLI_OPT_NET_READ_BUFFER_SIZE', 203);
define('MYSQLI_OPT_INT_AND_FLOAT_NATIVE', 201);








define('MYSQLI_CLIENT_SSL', 2048);







define('MYSQLI_CLIENT_COMPRESS', 32);











define('MYSQLI_CLIENT_INTERACTIVE', 1024);







define('MYSQLI_CLIENT_IGNORE_SPACE', 256);







define('MYSQLI_CLIENT_NO_SCHEMA', 16);
define('MYSQLI_CLIENT_FOUND_ROWS', 2);







define('MYSQLI_STORE_RESULT', 0);







define('MYSQLI_USE_RESULT', 1);
define('MYSQLI_ASYNC', 8);







define('MYSQLI_ASSOC', 1);







define('MYSQLI_NUM', 2);







define('MYSQLI_BOTH', 3);




define('MYSQLI_STMT_ATTR_UPDATE_MAX_LENGTH', 0);




define('MYSQLI_STMT_ATTR_CURSOR_TYPE', 1);




define('MYSQLI_CURSOR_TYPE_NO_CURSOR', 0);




define('MYSQLI_CURSOR_TYPE_READ_ONLY', 1);




define('MYSQLI_CURSOR_TYPE_FOR_UPDATE', 2);




define('MYSQLI_CURSOR_TYPE_SCROLLABLE', 4);




define('MYSQLI_STMT_ATTR_PREFETCH_ROWS', 2);







define('MYSQLI_NOT_NULL_FLAG', 1);







define('MYSQLI_PRI_KEY_FLAG', 2);







define('MYSQLI_UNIQUE_KEY_FLAG', 4);







define('MYSQLI_MULTIPLE_KEY_FLAG', 8);







define('MYSQLI_BLOB_FLAG', 16);







define('MYSQLI_UNSIGNED_FLAG', 32);







define('MYSQLI_ZEROFILL_FLAG', 64);







define('MYSQLI_AUTO_INCREMENT_FLAG', 512);







define('MYSQLI_TIMESTAMP_FLAG', 1024);







define('MYSQLI_SET_FLAG', 2048);







define('MYSQLI_NUM_FLAG', 32768);







define('MYSQLI_PART_KEY_FLAG', 16384);







define('MYSQLI_GROUP_FLAG', 32768);







define('MYSQLI_ENUM_FLAG', 256);
define('MYSQLI_BINARY_FLAG', 128);
define('MYSQLI_NO_DEFAULT_VALUE_FLAG', 4096);
define('MYSQLI_ON_UPDATE_NOW_FLAG', 8192);

define('MYSQLI_TRANS_START_READ_ONLY', 4);
define('MYSQLI_TRANS_START_READ_WRITE', 2);
define('MYSQLI_TRANS_START_WITH_CONSISTENT_SNAPSHOT', 1);






define('MYSQLI_TYPE_DECIMAL', 0);







define('MYSQLI_TYPE_TINY', 1);







define('MYSQLI_TYPE_SHORT', 2);







define('MYSQLI_TYPE_LONG', 3);







define('MYSQLI_TYPE_FLOAT', 4);







define('MYSQLI_TYPE_DOUBLE', 5);







define('MYSQLI_TYPE_NULL', 6);







define('MYSQLI_TYPE_TIMESTAMP', 7);







define('MYSQLI_TYPE_LONGLONG', 8);







define('MYSQLI_TYPE_INT24', 9);







define('MYSQLI_TYPE_DATE', 10);







define('MYSQLI_TYPE_TIME', 11);







define('MYSQLI_TYPE_DATETIME', 12);







define('MYSQLI_TYPE_YEAR', 13);







define('MYSQLI_TYPE_NEWDATE', 14);







define('MYSQLI_TYPE_ENUM', 247);







define('MYSQLI_TYPE_SET', 248);







define('MYSQLI_TYPE_TINY_BLOB', 249);







define('MYSQLI_TYPE_MEDIUM_BLOB', 250);







define('MYSQLI_TYPE_LONG_BLOB', 251);







define('MYSQLI_TYPE_BLOB', 252);







define('MYSQLI_TYPE_VAR_STRING', 253);







define('MYSQLI_TYPE_STRING', 254);







define('MYSQLI_TYPE_CHAR', 1);







define('MYSQLI_TYPE_INTERVAL', 247);







define('MYSQLI_TYPE_GEOMETRY', 255);







define('MYSQLI_TYPE_NEWDECIMAL', 246);







define('MYSQLI_TYPE_BIT', 16);




define('MYSQLI_SET_CHARSET_NAME', 7);







define('MYSQLI_NO_DATA', 100);







define('MYSQLI_DATA_TRUNCATED', 101);







define('MYSQLI_REPORT_INDEX', 4);







define('MYSQLI_REPORT_ERROR', 1);







define('MYSQLI_REPORT_STRICT', 2);







define('MYSQLI_REPORT_ALL', 255);







define('MYSQLI_REPORT_OFF', 0);







define('MYSQLI_DEBUG_TRACE_ENABLED', 0);




define('MYSQLI_SERVER_QUERY_NO_GOOD_INDEX_USED', 16);




define('MYSQLI_SERVER_QUERY_NO_INDEX_USED', 32);




define('MYSQLI_REFRESH_GRANT', 1);




define('MYSQLI_REFRESH_LOG', 2);




define('MYSQLI_REFRESH_TABLES', 4);




define('MYSQLI_REFRESH_HOSTS', 8);




define('MYSQLI_REFRESH_STATUS', 16);




define('MYSQLI_REFRESH_THREADS', 32);




define('MYSQLI_REFRESH_SLAVE', 64);




define('MYSQLI_REFRESH_MASTER', 128);

define('MYSQLI_SERVER_QUERY_WAS_SLOW', 2048);
define('MYSQLI_REFRESH_BACKUP_LOG', 2097152);




define('MYSQLI_OPT_SSL_VERIFY_SERVER_CERT', 21);

define('MYSQLI_SET_CHARSET_DIR', 6);

define('MYSQLI_SERVER_PS_OUT_PARAMS', 4096);

define('MYSQLI_CLIENT_SSL_VERIFY_SERVER_CERT', 1073741824);

define('MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT', 64);
define('MYSQLI_CLIENT_CAN_HANDLE_EXPIRED_PASSWORDS', 4194304);
define('MYSQLI_OPT_CAN_HANDLE_EXPIRED_PASSWORDS', 37);
define('MYSQLI_OPT_READ_TIMEOUT', 11);
define('MYSQLI_STORE_RESULT_COPY_DATA', 16);
define('MYSQLI_TYPE_JSON', 245);
define('MYSQLI_TRANS_COR_AND_CHAIN', 1);
define('MYSQLI_TRANS_COR_AND_NO_CHAIN', 2);
define('MYSQLI_TRANS_COR_RELEASE', 4);
define('MYSQLI_TRANS_COR_NO_RELEASE', 8);
define('MYSQLI_OPT_LOAD_DATA_LOCAL_DIR', 43);
define('MYSQLI_REFRESH_REPLICA', 64);



define('MYSQLI_IS_MARIADB', 0);
