<?php


use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;



































#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection|false'], default: 'resource|false')]
function pg_connect(
string $connection_string,
int $flags = 0,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] string $host = '',
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] string $port = '',
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] string $options = '',
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] string $tty = '',
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] string $dbname = '',
) {}






























#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection|false'], default: 'resource|false')]
function pg_pconnect(
string $connection_string,
#[PhpStormStubsElementAvailable(from: '8.0')] int $flags = 0,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] string $host = '',
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] string $port = '',
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] string $options = '',
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] string $tty = '',
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] string $dbname = '',
) {}












function pg_close(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection|null'], default: 'resource')] $connection = null): bool {}











function pg_connect_poll(
#[PhpStormStubsElementAvailable(from: '5.6', to: '7.4')] $connection = null,
#[PhpStormStubsElementAvailable(from: '8.0')] #[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection
): int {}










function pg_connection_status(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection): int {}









function pg_connection_busy(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection): bool {}









function pg_connection_reset(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection): bool {}










function pg_socket(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection) {}













function pg_host(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection|null'], default: 'resource')] $connection = null): string {}













function pg_dbname(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection|null'], default: 'resource')] $connection = null): string {}












function pg_port(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection|null'], default: 'resource')] $connection = null): string {}













function pg_tty(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection|null'], default: 'resource')] $connection = null): string {}













function pg_options(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection|null'], default: 'resource')] $connection = null): string {}














function pg_version(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection|null'], default: 'resource')] $connection = null): array {}












function pg_ping(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection|null'], default: 'resource')] $connection = null): bool {}




















function pg_parameter_status(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection, string $name = null): string|false {}















function pg_transaction_status(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection): int {}





























#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result|false'], default: 'resource|false')]
function pg_query(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $connection = null,
#[PhpStormStubsElementAvailable(from: '8.0')] #[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
string $query = null
) {}



































#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result|false'], default: 'resource|false')]
function pg_query_params(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $connection = null,
#[PhpStormStubsElementAvailable(from: '8.0')] #[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $query = '',
#[PhpStormStubsElementAvailable(from: '8.0')] $query,
array $params = null
) {}























#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result|false'], default: 'resource|false')]
function pg_prepare(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $connection = null,
#[PhpStormStubsElementAvailable(from: '8.0')] #[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] string $statement_name = '',
#[PhpStormStubsElementAvailable(from: '8.0')] string $statement_name,
string $query = null
) {}



























#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result|false'], default: 'resource|false')]
function pg_execute(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $connection = null,
#[PhpStormStubsElementAvailable(from: '8.0')] #[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] string $statement_name = '',
#[PhpStormStubsElementAvailable(from: '8.0')] $statement_name,
array $params = null
) {}

















function pg_send_query(
#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
string $query
): int|bool {}





















function pg_send_query_params(
#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
string $query,
array $params
): int|bool {}























function pg_send_prepare(
#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
string $statement_name,
string $query
): int|bool {}

























function pg_send_execute(
#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
string $statement_name,
array $params
): int|bool {}









function pg_cancel_query(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection): bool {}




























function pg_fetch_result(
#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $row = 0,
#[PhpStormStubsElementAvailable(from: '8.0')] $row,
string|int $field = null
): string|false|null {}






















function pg_fetch_row(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result, ?int $row = null, int $mode = 2): array|false {}






















function pg_fetch_assoc(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result, ?int $row = null): array|false {}



































function pg_fetch_array(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result, ?int $row = null, int $mode = PGSQL_BOTH): array|false {}


























function pg_fetch_object(
#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result,
?int $row = null,
string $class = 'stdClass',
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $l = null,
array $constructor_args = []
): object|false {}




























function pg_fetch_all(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result, int $mode = PGSQL_ASSOC): array {}



















function pg_fetch_all_columns(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result, int $field = 0): array {}












function pg_affected_rows(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result): int {}









#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result|false'], default: 'resource|false')]
function pg_get_result(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection) {}















function pg_result_seek(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result, int $row): bool {}





















function pg_result_status(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result, int $mode = PGSQL_STATUS_LONG): string|int {}











function pg_free_result(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result): bool {}













function pg_last_oid(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result): string|int|false {}











function pg_num_rows(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result): int {}











function pg_num_fields(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result): int {}














function pg_field_name(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result, int $field): string {}














function pg_field_num(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result, string $field): int {}















function pg_field_size(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result, int $field): int {}















function pg_field_type(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result, int $field): string {}














function pg_field_type_oid(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result, int $field): string|int {}













function pg_field_prtlen(
#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $row = 0,
#[PhpStormStubsElementAvailable(from: '8.0')] $row,
string|int $field = null
): int|false {}




















function pg_field_is_null(
#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $row = 0,
#[PhpStormStubsElementAvailable(from: '8.0')] $row,
string|int $field = null
): int|false {}



















function pg_field_table(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result, int $field, bool $oid_only = false): string|int|false {}






















function pg_get_notify(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $connection = null,
#[PhpStormStubsElementAvailable(from: '8.0')] #[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
int $mode = 1
): array|false {}









function pg_get_pid(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $connection = null,
#[PhpStormStubsElementAvailable(from: '8.0')] #[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
): int {}












function pg_result_error(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result): string|false {}






















function pg_result_error_field(
#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result,
int $field_code
): string|false|null {}













function pg_last_error(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection|null'], default: 'resource')] $connection = null): string {}


















function pg_last_notice(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection, int $mode = PGSQL_NOTICE_LAST): array|string|bool {}
















function pg_put_line(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $connection = null,
#[PhpStormStubsElementAvailable(from: '8.0')] #[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
string $query = null
): bool {}












function pg_end_copy(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection|null'], default: 'resource')] $connection = null): bool {}





















function pg_copy_to(
#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
string $table_name,
string $separator = '	',
string $null_as = '\\\\N'
): array|false {}


























function pg_copy_from(
#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
string $table_name,
array $rows,
string $separator = '	',
string $null_as = '\\\\N'
): bool {}



















function pg_trace(string $filename, string $mode = "w", #[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection|null'], default: 'resource')] $connection = null): bool {}












function pg_untrace(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection|null'], default: 'resource')] $connection = null): bool {}



















function pg_lo_create(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection = null, $oid = null): string|int|false {}















function pg_lo_unlink(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $connection = null,
#[PhpStormStubsElementAvailable(from: '8.0')] #[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
$oid = null
): bool {}



















#[LanguageLevelTypeAware(['8.1' => 'PgSql\Lob|false'], default: 'resource|false')]
function pg_lo_open(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $connection = null,
#[PhpStormStubsElementAvailable(from: '8.0')] #[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
$oid = null,
string $mode = null
) {}







function pg_lo_close(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Lob'], default: 'resource')] $lob): bool {}













function pg_lo_read(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Lob'], default: 'resource')] $lob, int $length = 8192): string|false {}



















function pg_lo_write(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Lob'], default: 'resource')] $lob, string $data, ?int $length = null): int|false {}









function pg_lo_read_all(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Lob'], default: 'resource')] $lob): int {}
























function pg_lo_import(
#[PhpStormStubsElementAvailable(from: '8.0')] #[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $connection = null,
$pathname,
$object_id = null
): string|int|false {}



















function pg_lo_export(
#[PhpStormStubsElementAvailable('8.0')] #[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $connection = null,
$oid,
$pathname
): bool {}

















function pg_lo_seek(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Lob'], default: 'resource')] $lob, int $offset, int $whence = PGSQL_SEEK_CUR): bool {}










function pg_lo_tell(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Lob'], default: 'resource')] $lob): int {}










function pg_lo_truncate(
#[LanguageLevelTypeAware(['8.1' => 'PgSql\Lob'], default: 'resource')] $lob,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] int $size = 0,
#[PhpStormStubsElementAvailable(from: '8.0')] int $size
): bool {}















function pg_escape_string(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $connection = null,
#[PhpStormStubsElementAvailable(from: '8.0')] #[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
string $string = null
): string {}
















function pg_escape_bytea(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $connection = null,
#[PhpStormStubsElementAvailable(from: '8.0')] #[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
string $string = null
): string {}
















function pg_escape_identifier(
#[PhpStormStubsElementAvailable(from: '5.4', to: '7.4')] $connection = null,
#[PhpStormStubsElementAvailable(from: '8.0')] #[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
string $string = null
): string|false {}
















function pg_escape_literal(
#[PhpStormStubsElementAvailable(from: '5.4', to: '7.4')] $connection = null,
#[PhpStormStubsElementAvailable(from: '8.0')] #[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
string $string = null
): string|false {}










function pg_unescape_bytea(string $string): string {}




















function pg_set_error_verbosity(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $connection = null,
#[PhpStormStubsElementAvailable(from: '8.0')] #[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
int $verbosity = null
): int|false {}












function pg_client_encoding(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection|null'], default: 'resource')] $connection = null): string {}























function pg_set_client_encoding(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $connection = null,
#[PhpStormStubsElementAvailable(from: '8.0')] #[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
string $encoding = null
): int {}












function pg_meta_data(
#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
string $table_name,
#[PhpStormStubsElementAvailable(from: '8.0')] bool $extended = false
): array|false {}




















function pg_convert(
#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
string $table_name,
array $values,
int $flags = 0
): array|false {}


























#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result|string|bool'], default: 'resource|string|bool')]
function pg_insert(
#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
string $table_name,
array $values,
int $flags = PGSQL_DML_EXEC
) {}




























function pg_update(
#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
string $table_name,
array $values,
array $conditions,
int $flags = PGSQL_DML_EXEC
): string|bool {}
























function pg_delete(
#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
string $table_name,
array $conditions,
int $flags = PGSQL_DML_EXEC
): string|bool {}





































function pg_select(
#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
string $table_name,
array $conditions,
int $flags = PGSQL_DML_EXEC,
int $mode = PGSQL_ASSOC
): array|string|false {}






#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result|false'], default: 'resource|false')]
function pg_exec(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $connection = null,
#[PhpStormStubsElementAvailable(from: '8.0')] #[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
string $query = null
) {}






function pg_getlastoid(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result): string|int|false {}






function pg_cmdtuples(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result): int {} 






function pg_errormessage(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection|null'], default: 'resource')] $connection): string {}






function pg_numrows(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result): int {}






function pg_numfields(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result): int {}







function pg_fieldname(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result, int $field): string {}







function pg_fieldsize(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result, int $field): int {}







function pg_fieldtype(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result, int $field): string {}







function pg_fieldnum(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result, string $field): int {}








function pg_fieldprtlen(
#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $row = 0,
#[PhpStormStubsElementAvailable(from: '8.0')] $row,
string|int $field
): int|false {}








function pg_fieldisnull(
#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $row = 0,
#[PhpStormStubsElementAvailable(from: '8.0')] $row,
string|int $field
): int|false {}






function pg_freeresult(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result): bool {}







function pg_result(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $connection,
#[PhpStormStubsElementAvailable(from: '8.0')] #[LanguageLevelTypeAware(['8.1' => 'PgSql\Result'], default: 'resource')] $result,
#[PhpStormStubsElementAvailable(from: '8.0')] $row,
#[PhpStormStubsElementAvailable(from: '8.0')] string|int $field = null
): string|null|false {}





function pg_loreadall(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Lob'], default: 'resource')] $lob): int {} 







function pg_locreate(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection, $oid): string|int|false {}







function pg_lounlink(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $connection = null,
#[PhpStormStubsElementAvailable(from: '8.0')] #[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
$oid
): bool {}








#[LanguageLevelTypeAware(['8.1' => 'PgSql\Lob|false'], default: 'resource|false')]
function pg_loopen(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $connection = null,
#[PhpStormStubsElementAvailable(from: '8.0')] #[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
$oid,
string $mode
) {}






function pg_loclose(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Lob'], default: 'resource')] $lob): bool {}







function pg_loread(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Lob'], default: 'resource')] $lob, int $length = 8192): string|false {}








function pg_lowrite(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Lob'], default: 'resource')] $lob, string $data, ?int $length): int|false {}








function pg_loimport(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $connection = null,
#[PhpStormStubsElementAvailable(from: '8.0')] #[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
$filename,
$oid
): string|int|false {}








function pg_loexport(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $connection = null,
#[PhpStormStubsElementAvailable(from: '8.0')] #[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
$oid,
$filename
): bool {}






function pg_clientencoding(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection|null'], default: 'resource')] $connection): string {}







function pg_setclientencoding(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $connection = null,
#[PhpStormStubsElementAvailable(from: '8.0')] #[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection,
string $encoding
): int {}








function pg_consume_input(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection): bool {}








function pg_flush(#[LanguageLevelTypeAware(['8.1' => 'PgSql\Connection'], default: 'resource')] $connection): int|bool {}

define('PGSQL_LIBPQ_VERSION', "14.4");
define('PGSQL_LIBPQ_VERSION_STR', "14.4");






define('PGSQL_CONNECT_FORCE_NEW', 2);






define('PGSQL_ASSOC', 1);






define('PGSQL_NUM', 2);






define('PGSQL_BOTH', 3);






define('PGSQL_CONNECTION_BAD', 1);






define('PGSQL_CONNECTION_OK', 0);






define('PGSQL_TRANSACTION_IDLE', 0);







define('PGSQL_TRANSACTION_ACTIVE', 1);






define('PGSQL_TRANSACTION_INTRANS', 2);






define('PGSQL_TRANSACTION_INERROR', 3);






define('PGSQL_TRANSACTION_UNKNOWN', 4);







define('PGSQL_ERRORS_TERSE', 0);








define('PGSQL_ERRORS_DEFAULT', 1);






define('PGSQL_ERRORS_VERBOSE', 2);






define('PGSQL_SEEK_SET', 0);






define('PGSQL_SEEK_CUR', 1);






define('PGSQL_SEEK_END', 2);






define('PGSQL_STATUS_LONG', 1);






define('PGSQL_STATUS_STRING', 2);






define('PGSQL_EMPTY_QUERY', 0);






define('PGSQL_COMMAND_OK', 1);






define('PGSQL_TUPLES_OK', 2);






define('PGSQL_COPY_OUT', 3);






define('PGSQL_COPY_IN', 4);






define('PGSQL_BAD_RESPONSE', 5);






define('PGSQL_NONFATAL_ERROR', 6);






define('PGSQL_FATAL_ERROR', 7);










define('PGSQL_DIAG_SEVERITY', 83);









define('PGSQL_DIAG_SQLSTATE', 67);






define('PGSQL_DIAG_MESSAGE_PRIMARY', 77);






define('PGSQL_DIAG_MESSAGE_DETAIL', 68);







define('PGSQL_DIAG_MESSAGE_HINT', 72);







define('PGSQL_DIAG_STATEMENT_POSITION', 80);










define('PGSQL_DIAG_INTERNAL_POSITION', 112);







define('PGSQL_DIAG_INTERNAL_QUERY', 113);









define('PGSQL_DIAG_CONTEXT', 87);







define('PGSQL_DIAG_SOURCE_FILE', 70);







define('PGSQL_DIAG_SOURCE_LINE', 76);






define('PGSQL_DIAG_SOURCE_FUNCTION', 82);






define('PGSQL_CONV_IGNORE_DEFAULT', 2);






define('PGSQL_CONV_FORCE_NULL', 4);






define('PGSQL_CONV_IGNORE_NOT_NULL', 8);
define('PGSQL_DML_NO_CONV', 256);
define('PGSQL_DML_EXEC', 512);
define('PGSQL_DML_ASYNC', 1024);
define('PGSQL_DML_STRING', 2048);





define('PGSQL_NOTICE_LAST', 1);





define('PGSQL_NOTICE_ALL', 2);





define('PGSQL_NOTICE_CLEAR', 3);

const PGSQL_CONNECT_ASYNC = 4;
const PGSQL_CONNECTION_AUTH_OK = 5;
const PGSQL_CONNECTION_AWAITING_RESPONSE = 4;
const PGSQL_CONNECTION_MADE = 3;
const PGSQL_CONNECTION_SETENV = 6;
const PGSQL_CONNECTION_STARTED = 2;
const PGSQL_DML_ESCAPE = 4096;
const PGSQL_POLLING_ACTIVE = 4;
const PGSQL_POLLING_FAILED = 0;
const PGSQL_POLLING_OK = 3;
const PGSQL_POLLING_READING = 1;
const PGSQL_POLLING_WRITING = 2;
const PGSQL_DIAG_SCHEMA_NAME = 115;
const PGSQL_DIAG_TABLE_NAME = 116;
const PGSQL_DIAG_COLUMN_NAME = 99;
const PGSQL_DIAG_DATATYPE_NAME = 100;
const PGSQL_DIAG_CONSTRAINT_NAME = 110;
const PGSQL_DIAG_SEVERITY_NONLOCALIZED = 86;

