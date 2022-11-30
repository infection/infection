<?php











namespace pq;

use pq;




class COPY
{



public const FROM_STDIN = 0;




public const TO_STDOUT = 1;

/**
@public
@readonly



*/
public $connection;

/**
@public
@readonly



*/
public $expression;

/**
@public
@readonly



*/
public $direction;

/**
@public
@readonly



*/
public $options;












public function __construct(pq\Connection $conn, string $expression, int $direction, string $options = null) {}









public function end(string $error = null) {}










public function get(string &$data) {}









public function put(string $data) {}
}



class Cancel
{
/**
@public
@readonly



*/
public $connection;









public function __construct(pq\Connection $conn) {}








public function cancel() {}
}





class Connection
{



public const PERSISTENT = 2;




public const ASYNC = 1;




public const OK = 0;




public const BAD = 1;




public const STARTED = 2;




public const MADE = 3;




public const AWAITING_RESPONSE = 4;




public const AUTH_OK = 5;




public const SSL_STARTUP = 7;




public const SETENV = 6;




public const TRANS_IDLE = 0;




public const TRANS_ACTIVE = 1;




public const TRANS_INTRANS = 2;




public const TRANS_INERROR = 3;




public const TRANS_UNKNOWN = 4;




public const POLLING_FAILED = 0;




public const POLLING_READING = 1;




public const POLLING_WRITING = 2;




public const POLLING_OK = 3;




public const EVENT_NOTICE = 'notice';




public const EVENT_RESULT = 'result';




public const EVENT_RESET = 'reset';

/**
@public
@readonly



*/
public $status;

/**
@public
@readonly



*/
public $transactionStatus;

/**
@public
@readonly



*/
public $socket;

/**
@public
@readonly



*/
public $busy;

/**
@public
@readonly



*/
public $errorMessage;

/**
@public
@readonly



*/
public $eventHandlers;

/**
@public



*/
public $encoding = null;

/**
@public



*/
public $unbuffered = false;

/**
@public





*/
public $nonblocking = false;

/**
@public
@readonly



*/
public $db;

/**
@public
@readonly



*/
public $user;

/**
@public
@readonly



*/
public $pass;

/**
@public
@readonly



*/
public $host;

/**
@public
@readonly



*/
public $port;

/**
@public
@readonly





*/
public $options;

/**
@public



*/
public $defaultFetchType = \pq\Result::FETCH_ARRAY;

/**
@public



*/
public $defaultAutoConvert = \pq\Result::CONV_ALL;

/**
@public



*/
public $defaultTransactionIsolation = \pq\Transaction::READ_COMMITTED;

/**
@public



*/
public $defaultTransactionReadonly = false;

/**
@public



*/
public $defaultTransactionDeferrable = false;











public function __construct(string $dsn = "", int $flags = 0) {}












public function declare(string $name, int $flags, string $query) {}















public function declareAsync(string $name, int $flags, string $query) {}












public function escapeBytea(string $binary) {}














public function exec(string $query) {}














public function execAsync(string $query, callable $callback = null) {}












public function execParams(string $query, array $params, array $types = null) {}
















public function execParamsAsync(string $query, array $params, array $types = null, callable $cb = null) {}

















public function flush() {}











public function getResult() {}












public function listen(string $channel, callable $listener) {}












public function listenAsync(string $channel, callable $listener) {}










public function notify(string $channel, string $message) {}










public function notifyAsync(string $channel, string $message) {}









public function off(string $event) {}











public function on(string $event, callable $callback) {}










public function poll() {}












public function prepare(string $name, string $query, array $types = null) {}















public function prepareAsync(string $name, string $query, array $types = null) {}













public function quote(string $payload) {}












public function quoteName(string $name) {}








public function reset() {}








public function resetAsync() {}








public function setConverter(pq\Converter $converter) {}




















public function startTransaction(int $isolation = \pq\Transaction::READ_COMMITTED, bool $readonly = false, bool $deferrable = false) {}




















public function startTransactionAsync(int $isolation = \pq\Transaction::READ_COMMITTED, bool $readonly = false, bool $deferrable = false) {}












public function trace($stream = null) {}









public function unescapeBytea(string $bytea) {}










public function unlisten(string $channel) {}










public function unlistenAsync(string $channel) {}








public function unsetConverter(pq\Converter $converter) {}
}



interface Converter
{







public function convertFromString(string $data, int $type);








public function convertToString($value, int $type);






public function convertTypes();
}



class Cursor
{



public const BINARY = 1;




public const INSENSITIVE = 2;




public const WITH_HOLD = 4;




public const SCROLL = 16;







public const NO_SCROLL = 32;

/**
@public
@readonly



*/
public $connection;

/**
@public
@readonly



*/
public $name;














public function __construct(pq\Connection $connection, string $name, int $flags, string $query, bool $async) {}









public function close() {}









public function closeAsync() {}















public function fetch(string $spec = "1") {}












public function fetchAsync(string $spec = "1", callable $callback = null) {}















public function move(string $spec = "1") {}












public function moveAsync(string $spec = "1", callable $callback = null) {}












public function open() {}









public function openAsync() {}
}



class DateTime extends \DateTime implements \JsonSerializable
{
/**
@public



*/
public $format = "Y-m-d H:i:s.uO";






public function __toString() {}







public function jsonSerialize() {}
}



interface Exception
{



public const INVALID_ARGUMENT = 0;




public const RUNTIME = 1;




public const CONNECTION_FAILED = 2;




public const IO = 3;




public const ESCAPE = 4;




public const UNINITIALIZED = 6;




public const BAD_METHODCALL = 5;




public const SQL = 8;




public const DOMAIN = 7;
}






class LOB
{



public const INVALID_OID = 0;




public const RW = 393216;

/**
@public
@readonly



*/
public $transaction;

/**
@public
@readonly



*/
public $oid;

/**
@public
@readonly



*/
public $stream;












public function __construct(pq\Transaction $txn, int $oid = \pq\LOB::INVALID_OID, int $mode = \pq\LOB::RW) {}











public function read(int $length = 0x1000, int &$read = null) {}











public function seek(int $offset, int $whence = SEEK_SET) {}









public function tell() {}









public function truncate(int $length = 0) {}







public function write(string $data) {}
}





class Result implements \Traversable, \Countable
{



public const EMPTY_QUERY = 0;




public const COMMAND_OK = 1;




public const TUPLES_OK = 2;




public const SINGLE_TUPLE = 9;




public const COPY_OUT = 3;




public const COPY_IN = 4;




public const COPY_BOTH = 8;




public const BAD_RESPONSE = 5;




public const NONFATAL_ERROR = 6;




public const FATAL_ERROR = 7;




public const FETCH_ARRAY = 0;




public const FETCH_ASSOC = 1;




public const FETCH_OBJECT = 2;




public const CONV_BOOL = 1;




public const CONV_INT = 2;




public const CONV_FLOAT = 4;




public const CONV_SCALAR = 15;




public const CONV_ARRAY = 16;




public const CONV_DATETIME = 32;




public const CONV_JSON = 256;




public const CONV_ALL = 65535;

/**
@public
@readonly



*/
public $status;

/**
@public
@readonly



*/
public $statusMessage;

/**
@public
@readonly



*/
public $errorMessage;

/**
@public
@readonly



*/
public $numRows;

/**
@public
@readonly



*/
public $numCols;

/**
@public
@readonly



*/
public $affectedRows;

/**
@public
@readonly



*/
public $diag;

/**
@public



*/
public $fetchType = \pq\Result::FETCH_ARRAY;

/**
@public



*/
public $autoConvert = \pq\Result::CONV_ALL;











public function bind($col, $var) {}








public function count() {}











public function desc() {}









public function fetchAll(int $fetch_type = null) {}










public function fetchAllCols(int $col = 0) {}











public function fetchBound() {}












public function fetchCol($ref, $col = 0) {}













public function fetchRow(int $fetch_type = null) {}












public function map($keys = 0, $vals = null, int $fetch_type = null) {}
}




class Statement
{
/**
@public
@readonly



*/
public $connection;

/**
@public
@readonly



*/
public $name;

/**
@public
@readonly



*/
public $query;

/**
@public
@readonly



*/
public $types;















public function __construct(pq\Connection $conn, string $name, string $query, array $types = null, bool $async = false) {}









public function bind(int $param_no, &$param_ref) {}









public function deallocate() {}









public function deallocateAsync() {}










public function desc() {}










public function descAsync(callable $callback) {}











public function exec(array $params = null) {}












public function execAsync(array $params = null, callable $cb = null) {}








public function prepare() {}









public function prepareAsync() {}
}






class Transaction
{



public const READ_COMMITTED = 0;




public const REPEATABLE_READ = 1;




public const SERIALIZABLE = 2;

/**
@public
@readonly



*/
public $connection;

/**
@public



*/
public $isolation = \pq\Transaction::READ_COMMITTED;

/**
@public



*/
public $readonly = false;

/**
@public



*/
public $deferrable = false;














public function __construct(pq\Connection $conn, bool $async = false, int $isolation = \pq\Transaction::READ_COMMITTED, bool $readonly = false, bool $deferrable = false) {}










public function commit() {}









public function commitAsync() {}











public function createLOB(int $mode = \pq\LOB::RW) {}











public function exportLOB(int $oid, string $path) {}











public function exportSnapshot() {}









public function exportSnapshotAsync() {}











public function importLOB(string $local_path, int $oid = \pq\LOB::INVALID_OID) {}














public function importSnapshot(string $snapshot_id) {}













public function importSnapshotAsync(string $snapshot_id) {}












public function openLOB(int $oid, int $mode = \pq\LOB::RW) {}










public function rollback() {}









public function rollbackAsync() {}











public function savepoint() {}









public function savepointAsync() {}











public function unlinkLOB(int $oid) {}
}




class Types implements \ArrayAccess
{



public const BOOL = 16;




public const BYTEA = 17;




public const CHAR = 18;




public const NAME = 19;




public const INT8 = 20;




public const INT2 = 21;




public const INT2VECTOR = 22;




public const INT4 = 23;




public const REGPROC = 24;




public const TEXT = 25;




public const OID = 26;




public const TID = 27;




public const XID = 28;




public const CID = 29;




public const OIDVECTOR = 30;




public const PG_TYPE = 71;




public const PG_ATTRIBUTE = 75;




public const PG_PROC = 81;




public const PG_CLASS = 83;




public const JSON = 114;




public const XML = 142;




public const XMLARRAY = 143;




public const JSONARRAY = 199;




public const PG_NODE_TREE = 194;




public const SMGR = 210;




public const POINT = 600;




public const LSEG = 601;




public const PATH = 602;




public const BOX = 603;




public const POLYGON = 604;




public const LINE = 628;




public const LINEARRAY = 629;




public const FLOAT4 = 700;




public const FLOAT8 = 701;




public const ABSTIME = 702;




public const RELTIME = 703;




public const TINTERVAL = 704;




public const UNKNOWN = 705;




public const CIRCLE = 718;




public const CIRCLEARRAY = 719;




public const MONEY = 790;




public const MONEYARRAY = 791;




public const MACADDR = 829;




public const INET = 869;




public const CIDR = 650;




public const BOOLARRAY = 1000;




public const BYTEAARRAY = 1001;




public const CHARARRAY = 1002;




public const NAMEARRAY = 1003;




public const INT2ARRAY = 1005;




public const INT2VECTORARRAY = 1006;




public const INT4ARRAY = 1007;




public const REGPROCARRAY = 1008;




public const TEXTARRAY = 1009;




public const OIDARRAY = 1028;




public const TIDARRAY = 1010;




public const XIDARRAY = 1011;




public const CIDARRAY = 1012;




public const OIDVECTORARRAY = 1013;




public const BPCHARARRAY = 1014;




public const VARCHARARRAY = 1015;




public const INT8ARRAY = 1016;




public const POINTARRAY = 1017;




public const LSEGARRAY = 1018;




public const PATHARRAY = 1019;




public const BOXARRAY = 1020;




public const FLOAT4ARRAY = 1021;




public const FLOAT8ARRAY = 1022;




public const ABSTIMEARRAY = 1023;




public const RELTIMEARRAY = 1024;




public const TINTERVALARRAY = 1025;




public const POLYGONARRAY = 1027;




public const ACLITEM = 1033;




public const ACLITEMARRAY = 1034;




public const MACADDRARRAY = 1040;




public const INETARRAY = 1041;




public const CIDRARRAY = 651;




public const CSTRINGARRAY = 1263;




public const BPCHAR = 1042;




public const VARCHAR = 1043;




public const DATE = 1082;




public const TIME = 1083;




public const TIMESTAMP = 1114;




public const TIMESTAMPARRAY = 1115;




public const DATEARRAY = 1182;




public const TIMEARRAY = 1183;




public const TIMESTAMPTZ = 1184;




public const TIMESTAMPTZARRAY = 1185;




public const INTERVAL = 1186;




public const INTERVALARRAY = 1187;




public const NUMERICARRAY = 1231;




public const TIMETZ = 1266;




public const TIMETZARRAY = 1270;




public const BIT = 1560;




public const BITARRAY = 1561;




public const VARBIT = 1562;




public const VARBITARRAY = 1563;




public const NUMERIC = 1700;




public const REFCURSOR = 1790;




public const REFCURSORARRAY = 2201;




public const REGPROCEDURE = 2202;




public const REGOPER = 2203;




public const REGOPERATOR = 2204;




public const REGCLASS = 2205;




public const REGTYPE = 2206;




public const REGPROCEDUREARRAY = 2207;




public const REGOPERARRAY = 2208;




public const REGOPERATORARRAY = 2209;




public const REGCLASSARRAY = 2210;




public const REGTYPEARRAY = 2211;




public const UUID = 2950;




public const UUIDARRAY = 2951;




public const TSVECTOR = 3614;




public const GTSVECTOR = 3642;




public const TSQUERY = 3615;




public const REGCONFIG = 3734;




public const REGDICTIONARY = 3769;




public const TSVECTORARRAY = 3643;




public const GTSVECTORARRAY = 3644;




public const TSQUERYARRAY = 3645;




public const REGCONFIGARRAY = 3735;




public const REGDICTIONARYARRAY = 3770;




public const TXID_SNAPSHOT = 2970;




public const TXID_SNAPSHOTARRAY = 2949;




public const INT4RANGE = 3904;




public const INT4RANGEARRAY = 3905;




public const NUMRANGE = 3906;




public const NUMRANGEARRAY = 3907;




public const TSRANGE = 3908;




public const TSRANGEARRAY = 3909;




public const TSTZRANGE = 3910;




public const TSTZRANGEARRAY = 3911;




public const DATERANGE = 3912;




public const DATERANGEARRAY = 3913;




public const INT8RANGE = 3926;




public const INT8RANGEARRAY = 3927;




public const RECORD = 2249;




public const RECORDARRAY = 2287;




public const CSTRING = 2275;




public const ANY = 2276;




public const ANYARRAY = 2277;




public const VOID = 2278;




public const TRIGGER = 2279;




public const EVENT_TRIGGER = 3838;




public const LANGUAGE_HANDLER = 2280;




public const INTERNAL = 2281;




public const OPAQUE = 2282;




public const ANYELEMENT = 2283;




public const ANYNONARRAY = 2776;




public const ANYENUM = 3500;




public const FDW_HANDLER = 3115;




public const ANYRANGE = 3831;

/**
@public
@readonly



*/
public $connection;










public function __construct(pq\Connection $conn, array $namespaces = null) {}









public function refresh(array $namespaces = null) {}
}

namespace pq\Exception;




class BadMethodCallException extends \BadMethodCallException implements \pq\Exception {}



class DomainException extends \DomainException implements \pq\Exception
{
/**
@public
@readonly



*/
public $sqlstate;
}



class InvalidArgumentException extends \InvalidArgumentException implements \pq\Exception {}



class RuntimeException extends \RuntimeException implements \pq\Exception {}
