<?php


use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Internal\TentativeType;
use JetBrains\PhpStorm\Pure;







class PDOException extends RuntimeException
{
#[LanguageLevelTypeAware(['8.1' => 'array|null'], default: '')]
public $errorInfo;
protected $code;
}





class PDO
{




public const PARAM_NULL = 0;





public const PARAM_INT = 1;





public const PARAM_STR = 2;





public const PARAM_LOB = 3;





public const PARAM_STMT = 4;





public const PARAM_BOOL = 5;






public const PARAM_STR_NATL = 1073741824;






public const PARAM_STR_CHAR = 536870912;






public const ATTR_DEFAULT_STR_PARAM = 21;






public const SQLITE_DETERMINISTIC = 2048;




public const SQLITE_OPEN_READONLY = 1;




public const SQLITE_OPEN_READWRITE = 2;




public const SQLITE_OPEN_CREATE = 4;




public const SQLITE_ATTR_OPEN_FLAGS = 1000;







public const PARAM_INPUT_OUTPUT = 2147483648;





public const PARAM_EVT_ALLOC = 0;





public const PARAM_EVT_FREE = 1;





public const PARAM_EVT_EXEC_PRE = 2;





public const PARAM_EVT_EXEC_POST = 3;





public const PARAM_EVT_FETCH_PRE = 4;





public const PARAM_EVT_FETCH_POST = 5;






public const PARAM_EVT_NORMALIZE = 6;








public const FETCH_LAZY = 1;









public const FETCH_ASSOC = 2;







public const FETCH_NUM = 3;







public const FETCH_BOTH = 4;







public const FETCH_OBJ = 5;








public const FETCH_BOUND = 6;






public const FETCH_COLUMN = 7;









public const FETCH_CLASS = 8;






public const FETCH_INTO = 9;






public const FETCH_FUNC = 10;







public const FETCH_GROUP = 65536;





public const FETCH_UNIQUE = 196608;







public const FETCH_KEY_PAIR = 12;





public const FETCH_CLASSTYPE = 262144;







public const FETCH_SERIALIZE = 524288;






public const FETCH_PROPS_LATE = 1048576;









public const FETCH_NAMED = 11;






public const ATTR_AUTOCOMMIT = 0;








public const ATTR_PREFETCH = 1;





public const ATTR_TIMEOUT = 2;





public const ATTR_ERRMODE = 3;






public const ATTR_SERVER_VERSION = 4;






public const ATTR_CLIENT_VERSION = 5;






public const ATTR_SERVER_INFO = 6;
public const ATTR_CONNECTION_STATUS = 7;






public const ATTR_CASE = 8;






public const ATTR_CURSOR_NAME = 9;









public const ATTR_CURSOR = 10;





public const ATTR_ORACLE_NULLS = 11;






public const ATTR_PERSISTENT = 12;





public const ATTR_STATEMENT_CLASS = 13;








public const ATTR_FETCH_TABLE_NAMES = 14;








public const ATTR_FETCH_CATALOG_NAMES = 15;













public const ATTR_DRIVER_NAME = 16;





public const ATTR_STRINGIFY_FETCHES = 17;





public const ATTR_MAX_COLUMN_LEN = 18;





public const ATTR_EMULATE_PREPARES = 20;





public const ATTR_DEFAULT_FETCH_MODE = 19;







public const ERRMODE_SILENT = 0;






public const ERRMODE_WARNING = 1;






public const ERRMODE_EXCEPTION = 2;





public const CASE_NATURAL = 0;





public const CASE_LOWER = 2;





public const CASE_UPPER = 1;




public const NULL_NATURAL = 0;




public const NULL_EMPTY_STRING = 1;




public const NULL_TO_STRING = 2;










public const ERR_NONE = '00000';





public const FETCH_ORI_NEXT = 0;






public const FETCH_ORI_PRIOR = 1;





public const FETCH_ORI_FIRST = 2;





public const FETCH_ORI_LAST = 3;






public const FETCH_ORI_ABS = 4;






public const FETCH_ORI_REL = 5;






public const FETCH_DEFAULT = 0;







public const CURSOR_FWDONLY = 0;






public const CURSOR_SCROLL = 1;



















public const MYSQL_ATTR_USE_BUFFERED_QUERY = 1000;











public const MYSQL_ATTR_LOCAL_INFILE = 1001;












public const MYSQL_ATTR_INIT_COMMAND = 1002;








public const MYSQL_ATTR_MAX_BUFFER_SIZE = 1005;










public const MYSQL_ATTR_READ_DEFAULT_FILE = 1003;










public const MYSQL_ATTR_READ_DEFAULT_GROUP = 1004;








public const MYSQL_ATTR_COMPRESS = 1003;







public const MYSQL_ATTR_DIRECT_QUERY = 1004;








public const MYSQL_ATTR_FOUND_ROWS = 1005;








public const MYSQL_ATTR_IGNORE_SPACE = 1006;
public const MYSQL_ATTR_SERVER_PUBLIC_KEY = 1012;








public const MYSQL_ATTR_SSL_KEY = 1007;








public const MYSQL_ATTR_SSL_CERT = 1008;








public const MYSQL_ATTR_SSL_CA = 1009;









public const MYSQL_ATTR_SSL_CAPATH = 1010;










public const MYSQL_ATTR_SSL_CIPHER = 1011;











public const MYSQL_ATTR_MULTI_STATEMENTS = 1013;









public const MYSQL_ATTR_SSL_VERIFY_SERVER_CERT = 1014;




public const MYSQL_ATTR_LOCAL_INFILE_DIRECTORY = 1015;

#[Deprecated("Use PDO::ATTR_EMULATE_PREPARES instead")]
public const PGSQL_ASSOC = 1;

/**
@removed
*/
public const PGSQL_ATTR_DISABLE_NATIVE_PREPARED_STATEMENT = 1000;




public const PGSQL_ATTR_DISABLE_PREPARES = 1000;
public const PGSQL_BAD_RESPONSE = 5;
public const PGSQL_BOTH = 3;
public const PGSQL_TRANSACTION_IDLE = 0;
public const PGSQL_TRANSACTION_ACTIVE = 1;
public const PGSQL_TRANSACTION_INTRANS = 2;
public const PGSQL_TRANSACTION_INERROR = 3;
public const PGSQL_TRANSACTION_UNKNOWN = 4;
public const PGSQL_CONNECT_ASYNC = 4;
public const PGSQL_CONNECT_FORCE_NEW = 2;
public const PGSQL_CONNECTION_AUTH_OK = 5;
public const PGSQL_CONNECTION_AWAITING_RESPONSE = 4;
public const PGSQL_CONNECTION_BAD = 1;
public const PGSQL_CONNECTION_OK = 0;
public const PGSQL_CONNECTION_MADE = 3;
public const PGSQL_CONNECTION_SETENV = 6;
public const PGSQL_CONNECTION_SSL_STARTUP = 7;
public const PGSQL_CONNECTION_STARTED = 2;
public const PGSQL_COMMAND_OK = 1;
public const PGSQL_CONV_FORCE_NULL = 4;
public const PGSQL_CONV_IGNORE_DEFAULT = 2;
public const PGSQL_CONV_IGNORE_NOT_NULL = 8;
public const PGSQL_COPY_IN = 4;
public const PGSQL_COPY_OUT = 3;
public const PGSQL_DIAG_CONTEXT = 87;
public const PGSQL_DIAG_INTERNAL_POSITION = 112;
public const PGSQL_DIAG_INTERNAL_QUERY = 113;
public const PGSQL_DIAG_MESSAGE_DETAIL = 68;
public const PGSQL_DIAG_MESSAGE_HINT = 72;
public const PGSQL_DIAG_MESSAGE_PRIMARY = 77;
public const PGSQL_DIAG_SEVERITY = 83;
public const PGSQL_DIAG_SOURCE_FILE = 70;
public const PGSQL_DIAG_SOURCE_FUNCTION = 82;
public const PGSQL_DIAG_SOURCE_LINE = 76;
public const PGSQL_DIAG_SQLSTATE = 67;
public const PGSQL_DIAG_STATEMENT_POSITION = 80;
public const PGSQL_DML_ASYNC = 1024;
public const PGSQL_DML_EXEC = 512;
public const PGSQL_DML_NO_CONV = 256;
public const PGSQL_DML_STRING = 2048;
public const PGSQL_DML_ESCAPE = 4096;
public const PGSQL_EMPTY_QUERY = 0;
public const PGSQL_ERRORS_DEFAULT = 1;
public const PGSQL_ERRORS_TERSE = 0;
public const PGSQL_ERRORS_VERBOSE = 2;
public const PGSQL_FATAL_ERROR = 7;
public const PGSQL_NONFATAL_ERROR = 6;
public const PGSQL_NOTICE_ALL = 2;
public const PGSQL_NOTICE_CLEAR = 3;
public const PGSQL_NOTICE_LAST = 1;
public const PGSQL_NUM = 2;
public const PGSQL_POLLING_ACTIVE = 4;
public const PGSQL_POLLING_FAILED = 0;
public const PGSQL_POLLING_OK = 3;
public const PGSQL_POLLING_READING = 1;
public const PGSQL_POLLING_WRITING = 2;
public const PGSQL_SEEK_CUR = 1;
public const PGSQL_SEEK_END = 2;
public const PGSQL_SEEK_SET = 0;
public const PGSQL_STATUS_LONG = 1;
public const PGSQL_STATUS_STRING = 2;
public const PGSQL_TUPLES_OK = 2;
public const SQLSRV_TXN_READ_UNCOMMITTED = "READ_UNCOMMITTED";
public const SQLSRV_TXN_READ_COMMITTED = "READ_COMMITTED";
public const SQLSRV_TXN_REPEATABLE_READ = "REPEATABLE_READ";
public const SQLSRV_TXN_SNAPSHOT = "SNAPSHOT";
public const SQLSRV_TXN_SERIALIZABLE = "SERIALIZABLE";
public const SQLSRV_ENCODING_BINARY = 2;
public const SQLSRV_ENCODING_SYSTEM = 3;
public const SQLSRV_ENCODING_UTF8 = 65001;
public const SQLSRV_ENCODING_DEFAULT = 1;
public const SQLSRV_ATTR_ENCODING = 1000;
public const SQLSRV_ATTR_QUERY_TIMEOUT = 1001;
public const SQLSRV_ATTR_DIRECT_QUERY = 1002;
public const SQLSRV_ATTR_CURSOR_SCROLL_TYPE = 1003;
public const SQLSRV_ATTR_CLIENT_BUFFER_MAX_KB_SIZE = 1004;
public const SQLSRV_ATTR_FETCHES_NUMERIC_TYPE = 1005;
public const SQLSRV_ATTR_FETCHES_DATETIME_TYPE = 1006;
public const SQLSRV_ATTR_FORMAT_DECIMALS = 1007;
public const SQLSRV_ATTR_DECIMAL_PLACES = 1008;
public const SQLSRV_ATTR_DATA_CLASSIFICATION = 1009;
public const SQLSRV_PARAM_OUT_DEFAULT_SIZE = -1;
public const SQLSRV_CURSOR_KEYSET = 1;
public const SQLSRV_CURSOR_DYNAMIC = 2;
public const SQLSRV_CURSOR_STATIC = 3;
public const SQLSRV_CURSOR_BUFFERED = 42;




public const SQLITE_ATTR_READONLY_STATEMENT = 1001;




public const SQLITE_ATTR_EXTENDED_RESULT_CODES = 1002;






public const OCI_ATTR_ACTION = 1000;






public const OCI_ATTR_CLIENT_INFO = 1001;






public const OCI_ATTR_CLIENT_IDENTIFIER = 1002;






public const OCI_ATTR_MODULE = 1003;





public const OCI_ATTR_CALL_TIMEOUT = 1004;




public const FB_ATTR_DATE_FORMAT = 1000;




public const FB_ATTR_TIME_FORMAT = 1001;




public const FB_ATTR_TIMESTAMP_FORMAT = 1002;











public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $dsn,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $username = null,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $password = null,
#[LanguageLevelTypeAware(['8.0' => 'array|null'], default: '')] $options = null
) {}




























#[TentativeType]
public function prepare(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $query, array $options = []): PDOStatement|false {}

























#[TentativeType]
public function beginTransaction(): bool {}








#[TentativeType]
public function commit(): bool {}








#[TentativeType]
public function rollBack(): bool {}







#[TentativeType]
public function inTransaction(): bool {}









#[TentativeType]
public function setAttribute(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $attribute,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $value
): bool {}





























#[TentativeType]
public function exec(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $statement): int|false {}

























#[PhpStormStubsElementAvailable(to: '7.4')]
public function query($statement, $mode = PDO::ATTR_DEFAULT_FETCH_MODE, $arg3 = null, array $ctorargs = []) {}






















#[PhpStormStubsElementAvailable('8.0')]
public function query($statement, $mode = PDO::ATTR_DEFAULT_FETCH_MODE, ...$fetch_mode_args) {}
























#[TentativeType]
public function lastInsertId(#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $name = null): string|false {}



























#[TentativeType]
public function errorCode(): ?string {}








































#[ArrayShape([0 => "string", 1 => "int", 2 => "string"])]
#[TentativeType]
public function errorInfo(): array {}
























#[TentativeType]
public function getAttribute(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $attribute): mixed {}















#[TentativeType]
public function quote(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $string,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $type = PDO::PARAM_STR
): string|false {}

final public function __wakeup() {}

final public function __sleep() {}








#[TentativeType]
public static function getAvailableDrivers(): array {}






















public function sqliteCreateFunction($function_name, $callback, $num_args = -1, $flags = 0) {}






















public function pgsqlCopyFromArray(string $tableName, array $rows, string $separator = "\t", string $nullAs = "\\\\N", ?string $fields = null): bool {}






















public function pgsqlCopyFromFile(string $tableName, string $filename, string $separator = "\t", string $nullAs = "\\\\N", ?string $fields = null): bool {}



















public function pgsqlCopyToArray(string $tableName, string $separator = "\t", string $nullAs = "\\\\N", ?string $fields = null): array|false {}






















public function pgsqlCopyToFile(string $tableName, string $filename, string $separator = "\t", string $nullAs = "\\\\N", ?string $fields = null): bool {}








public function pgsqlLOBCreate(): string|false {}













public function pgsqlLOBOpen(string $oid, string $mode = "rb") {}










public function pgsqlLOBUnlink(string $oid): bool {}














public function pgsqlGetNotify(int $fetchMode = PDO::FETCH_DEFAULT, int $timeoutMilliseconds = 0): array|false {}







public function pgsqlGetPid(): int {}
}







class PDOStatement implements IteratorAggregate
{



#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $queryString;
























#[TentativeType]
public function execute(#[LanguageLevelTypeAware(['8.0' => 'array|null'], default: '')] $params = null): bool {}





























#[TentativeType]
public function fetch(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $mode = PDO::FETCH_BOTH,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $cursorOrientation = PDO::FETCH_ORI_NEXT,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $cursorOffset = 0
): mixed {}































#[TentativeType]
public function bindParam(
#[LanguageLevelTypeAware(['8.0' => 'int|string'], default: '')] $param,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] &$var,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $type = PDO::PARAM_STR,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $maxLength = null,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $driverOptions = null
): bool {}
























#[TentativeType]
public function bindColumn(
#[LanguageLevelTypeAware(['8.0' => 'int|string'], default: '')] $column,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] &$var,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $type = PDO::PARAM_STR,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $maxLength = null,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $driverOptions = null
): bool {}





















#[TentativeType]
public function bindValue(
#[LanguageLevelTypeAware(['8.0' => 'int|string'], default: '')] $param,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $value,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $type = PDO::PARAM_STR
): bool {}







#[TentativeType]
public function rowCount(): int {}

















#[TentativeType]
public function fetchColumn(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $column = 0): mixed {}













































#[TentativeType]
public function fetchAll(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $mode = PDO::FETCH_BOTH,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $fetch_argument = null,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] ...$args
): array {}

/**
@template












*/
#[TentativeType]
public function fetchObject(#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $class = "stdClass", array $constructorArgs = []): object|false {}









#[TentativeType]
public function errorCode(): ?string {}


























#[ArrayShape([0 => "string", 1 => "int", 2 => "string"])]
#[TentativeType]
public function errorInfo(): array {}









#[TentativeType]
public function setAttribute(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $attribute,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $value
): bool {}








#[TentativeType]
public function getAttribute(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $name): mixed {}









#[TentativeType]
public function columnCount(): int {}





























































#[TentativeType]
public function getColumnMeta(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $column): array|false {}














#[PhpStormStubsElementAvailable(to: '7.4')]
public function setFetchMode($mode, $className = null, array $params = []) {}














#[PhpStormStubsElementAvailable('8.0')]
public function setFetchMode($mode, $className = null, ...$params) {}







#[TentativeType]
public function nextRowset(): bool {}







#[TentativeType]
public function closeCursor(): bool {}







#[TentativeType]
public function debugDumpParams(): ?bool {}

final public function __wakeup() {}

final public function __sleep() {}





public function getIterator(): Iterator {}
}

final class PDORow
{
#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $queryString;
}








#[Pure]
function pdo_drivers(): array {}


