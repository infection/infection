<?php



























namespace {







final class Cassandra
{








public const CONSISTENCY_ANY = 0;








public const CONSISTENCY_ONE = 1;








public const CONSISTENCY_TWO = 2;








public const CONSISTENCY_THREE = 3;












public const CONSISTENCY_QUORUM = 4;








public const CONSISTENCY_ALL = 5;








public const CONSISTENCY_LOCAL_QUORUM = 6;









public const CONSISTENCY_EACH_QUORUM = 7;













public const CONSISTENCY_SERIAL = 8;








public const CONSISTENCY_LOCAL_SERIAL = 9;








public const CONSISTENCY_LOCAL_ONE = 10;







public const VERIFY_NONE = 0;







public const VERIFY_PEER_CERT = 1;









public const VERIFY_PEER_IDENTITY = 2;





public const BATCH_LOGGED = 0;





public const BATCH_UNLOGGED = 1;





public const BATCH_COUNTER = 2;





public const LOG_DISABLED = 0;





public const LOG_CRITICAL = 1;





public const LOG_ERROR = 2;





public const LOG_WARN = 3;





public const LOG_INFO = 4;





public const LOG_DEBUG = 5;





public const LOG_TRACE = 6;










public const TYPE_TEXT = 'text';










public const TYPE_ASCII = 'ascii';










public const TYPE_VARCHAR = 'varchar';










public const TYPE_BIGINT = 'bigint';










public const TYPE_SMALLINT = 'smallint';










public const TYPE_TINYINT = 'tinyint';










public const TYPE_BLOB = 'blob';










public const TYPE_BOOLEAN = 'boolean';










public const TYPE_COUNTER = 'counter';










public const TYPE_DECIMAL = 'decimal';










public const TYPE_DOUBLE = 'double';










public const TYPE_FLOAT = 'float';










public const TYPE_INT = 'int';










public const TYPE_TIMESTAMP = 'timestamp';










public const TYPE_UUID = 'uuid';










public const TYPE_VARINT = 'varint';










public const TYPE_TIMEUUID = 'timeuuid';










public const TYPE_INET = 'inet';





public const VERSION = '1.3.2';





public const CPP_DRIVER_VERSION = '2.13.0';







public static function cluster() {}







public static function ssl() {}
}
}





namespace Cassandra {
use JetBrains\PhpStorm\Deprecated;





interface Column
{






public function name();







public function type();







public function isReversed();







public function isStatic();







public function isFrozen();







public function indexName();







public function indexOptions();
}








interface Session
{























public function execute($statement, $options);













public function executeAsync($statement, $options);














public function prepare($cql, $options);












public function prepareAsync($cql, $options);











public function close($timeout);







public function closeAsync();







public function metrics();







public function schema();
}





interface Table
{






public function name();









public function option($name);







public function options();







public function comment();







public function readRepairChance();







public function localReadRepairChance();







public function gcGraceSeconds();







public function caching();







public function bloomFilterFPChance();







public function memtableFlushPeriodMs();







public function defaultTTL();







public function speculativeRetry();







public function indexInterval();







public function compactionStrategyClassName();







public function compactionStrategyOptions();







public function compressionParameters();







public function populateIOCacheOnFlush();







public function replicateOnWrite();







public function maxIndexInterval();







public function minIndexInterval();









public function column($name);







public function columns();







public function partitionKey();







public function primaryKey();







public function clusteringKey();





public function clusteringOrder();
}





interface RetryPolicy {}





interface TimestampGenerator {}







interface Exception {}





interface Function_
{






public function name();







public function simpleName();







public function arguments();







public function returnType();







public function signature();







public function language();







public function body();







public function isCalledOnNullInput();
}





interface UuidInterface
{






public function uuid();







public function version();
}





interface Index
{






public function name();







public function kind();







public function target();









public function option($name);







public function options();







public function className();







public function isCustom();
}





interface Cluster
{








public function connect($keyspace);









public function connectAsync($keyspace);
}











interface Numeric
{






public function add($num);







public function sub($num);







public function mul($num);







public function div($num);







public function mod($num);





public function abs();





public function neg();





public function sqrt();





public function toInt();





public function toDouble();
}










interface Future
{











public function get($timeout);
}





interface Keyspace
{






public function name();







public function replicationClassName();







public function replicationOptions();







public function hasDurableWrites();









public function table($name);







public function tables();









public function userType($name);







public function userTypes();









public function materializedView($name);







public function materializedViews();










public function function_($name, ...$params);







public function functions();










public function aggregate($name, ...$params);







public function aggregates();
}
























interface Value
{






public function type();
}





interface Aggregate
{






public function name();







public function simpleName();







public function argumentTypes();







public function finalFunction();







public function stateFunction();







public function initialCondition();







public function returnType();







public function stateType();







public function signature();
}









interface Statement {}





interface Schema
{








public function keyspace($name);







public function keyspaces();
}





final class Rows implements \Iterator, \ArrayAccess
{



public function __construct() {}









public function count() {}









public function rewind() {}









public function current() {}









public function key() {}









public function next() {}









public function valid() {}











public function offsetExists($offset) {}











public function offsetGet($offset) {}














public function offsetSet($offset, $value) {}













public function offsetUnset($offset) {}







public function isLastPage() {}









public function nextPage($timeout) {}







public function nextPageAsync() {}







public function pagingStateToken() {}







public function first() {}
}







final class DefaultCluster implements Cluster
{









public function connect($keyspace, $timeout) {}









public function connectAsync($keyspace) {}
}





final class DefaultFunction implements Function_
{






public function name() {}







public function simpleName() {}







public function arguments() {}







public function returnType() {}







public function signature() {}







public function language() {}







public function body() {}







public function isCalledOnNullInput() {}
}











final class SimpleStatement implements Statement
{







public function __construct($cql) {}
}





final class Tuple implements Value, \Countable, \Iterator
{







public function __construct($types) {}







public function type() {}







public function values() {}









public function set($value) {}









public function get($index) {}







public function count() {}







public function current() {}







public function key() {}







public function next() {}







public function valid() {}







public function rewind() {}
}





final class Smallint implements Value, Numeric
{







public function __construct($value) {}







public static function min() {}







public static function max() {}





public function __toString() {}







public function type() {}







public function value() {}







public function add($num) {}







public function sub($num) {}







public function mul($num) {}







public function div($num) {}







public function mod($num) {}





public function abs() {}





public function neg() {}





public function sqrt() {}





public function toInt() {}





public function toDouble() {}
}








final class FuturePreparedStatement implements Future
{











public function get($timeout) {}
}





final class DefaultSchema implements Schema
{








public function keyspace($name) {}







public function keyspaces() {}







public function version() {}
}





















final class BatchStatement implements Statement
{







public function __construct($type) {}












public function add($statement, $arguments) {}
}





final class Collection implements Value, \Countable, \Iterator
{







public function __construct($type) {}







public function type() {}







public function values() {}









public function add(...$value) {}









public function get($index) {}









public function find($value) {}







public function count() {}







public function current() {}







public function key() {}







public function next() {}







public function valid() {}







public function rewind() {}









public function remove($index) {}
}







final class FutureRows implements Future
{











public function get($timeout) {}
}





final class DefaultMaterializedView extends MaterializedView
{






public function name() {}









public function option($name) {}







public function options() {}







public function comment() {}







public function readRepairChance() {}







public function localReadRepairChance() {}







public function gcGraceSeconds() {}







public function caching() {}







public function bloomFilterFPChance() {}







public function memtableFlushPeriodMs() {}







public function defaultTTL() {}







public function speculativeRetry() {}







public function indexInterval() {}







public function compactionStrategyClassName() {}







public function compactionStrategyOptions() {}







public function compressionParameters() {}







public function populateIOCacheOnFlush() {}







public function replicateOnWrite() {}







public function maxIndexInterval() {}







public function minIndexInterval() {}









public function column($name) {}







public function columns() {}







public function partitionKey() {}







public function primaryKey() {}







public function clusteringKey() {}





public function clusteringOrder() {}







public function baseTable() {}
}







final class SSLOptions {}





final class Bigint implements Value, Numeric
{







public function __construct($value) {}







public static function min() {}







public static function max() {}







public function __toString() {}







public function type() {}







public function value() {}







public function add($num) {}







public function sub($num) {}







public function mul($num) {}







public function div($num) {}







public function mod($num) {}





public function abs() {}





public function neg() {}





public function sqrt() {}





public function toInt() {}





public function toDouble() {}
}







final class FutureSession implements Future
{











public function get($timeout) {}
}





final class Set implements Value, \Countable, \Iterator
{







public function __construct($type) {}







public function type() {}







public function values() {}









public function add($value) {}









public function has($value) {}









public function remove($value) {}







public function count() {}







public function current() {}







public function key() {}







public function next() {}







public function valid() {}







public function rewind() {}
}





final class DefaultIndex implements Index
{






public function name() {}







public function kind() {}







public function target() {}









public function option($name) {}







public function options() {}







public function className() {}







public function isCustom() {}
}





final class DefaultAggregate implements Aggregate
{






public function name() {}







public function simpleName() {}







public function argumentTypes() {}







public function stateFunction() {}







public function finalFunction() {}







public function initialCondition() {}







public function stateType() {}







public function returnType() {}







public function signature() {}
}





final class Timestamp implements Value
{









public function __construct($seconds, $microseconds) {}







public function type() {}









public function time() {}











public function microtime($get_as_float) {}







public function toDateTime() {}







public function __toString() {}
}





final class Tinyint implements Value, Numeric
{







public function __construct($value) {}







public static function min() {}







public static function max() {}





public function __toString() {}







public function type() {}







public function value() {}







public function add($num) {}







public function sub($num) {}







public function mul($num) {}







public function div($num) {}







public function mod($num) {}





public function abs() {}





public function neg() {}





public function sqrt() {}





public function toInt() {}





public function toDouble() {}
}





final class Timeuuid implements Value, UuidInterface
{







public function __construct($timestamp) {}







public function __toString() {}







public function type() {}







public function uuid() {}







public function version() {}









public function time() {}







public function toDateTime() {}
}








final class DefaultSession implements Session
{























public function execute($statement, $options) {}













public function executeAsync($statement, $options) {}














public function prepare($cql, $options) {}












public function prepareAsync($cql, $options) {}











public function close($timeout) {}







public function closeAsync() {}







public function metrics() {}







public function schema() {}
}





abstract class Custom implements Value
{






abstract public function type();
}





abstract class MaterializedView implements Table
{






abstract public function baseTable();







abstract public function name();









abstract public function option($name);








abstract public function options();







abstract public function comment();







abstract public function readRepairChance();







abstract public function localReadRepairChance();







abstract public function gcGraceSeconds();







abstract public function caching();







abstract public function bloomFilterFPChance();







abstract public function memtableFlushPeriodMs();







abstract public function defaultTTL();







abstract public function speculativeRetry();







abstract public function indexInterval();







abstract public function compactionStrategyClassName();







abstract public function compactionStrategyOptions();







abstract public function compressionParameters();







abstract public function populateIOCacheOnFlush();







abstract public function replicateOnWrite();







abstract public function maxIndexInterval();







abstract public function minIndexInterval();









abstract public function column($name);







abstract public function columns();







abstract public function partitionKey();







abstract public function primaryKey();







abstract public function clusteringKey();





abstract public function clusteringOrder();
}





final class Time implements Value
{







public function __construct($nanoseconds) {}







public static function fromDateTime($datetime) {}







public function type() {}





public function seconds() {}





public function __toString() {}
}





abstract class Type
{






final public static function ascii() {}







final public static function bigint() {}







final public static function smallint() {}







final public static function tinyint() {}







final public static function blob() {}







final public static function boolean() {}







final public static function counter() {}







final public static function decimal() {}







final public static function double() {}







final public static function duration() {}







final public static function float() {}







final public static function int() {}







final public static function text() {}







final public static function timestamp() {}







final public static function date() {}







final public static function time() {}







final public static function uuid() {}







final public static function varchar() {}







final public static function varint() {}







final public static function timeuuid() {}







final public static function inet() {}


















final public static function collection($type) {}


















final public static function set($type) {}

















final public static function map($keyType, $valueType) {}
















final public static function tuple($types) {}
















final public static function userType($types) {}







abstract public function name();







abstract public function __toString();
}





final class Varint implements Value, Numeric
{







public function __construct($value) {}







public function __toString() {}







public function type() {}







public function value() {}







public function add($num) {}







public function sub($num) {}







public function mul($num) {}







public function div($num) {}







public function mod($num) {}





public function abs() {}





public function neg() {}





public function sqrt() {}





public function toInt() {}





public function toDouble() {}
}





final class Map implements Value, \Countable, \Iterator, \ArrayAccess
{








public function __construct($keyType, $valueType) {}







public function type() {}







public function keys() {}







public function values() {}










public function set($key, $value) {}









public function get($key) {}









public function remove($key) {}









public function has($key) {}







public function count() {}







public function current() {}







public function key() {}







public function next() {}







public function valid() {}







public function rewind() {}












public function offsetSet($key, $value) {}











public function offsetGet($key) {}











public function offsetUnset($key) {}











public function offsetExists($key) {}
}





final class Uuid implements Value, UuidInterface
{







public function __construct($uuid) {}







public function __toString() {}







public function type() {}







public function uuid() {}







public function version() {}
}





final class Float_ implements Value, Numeric
{







public function __construct($value) {}







public static function min() {}







public static function max() {}







public function __toString() {}







public function type() {}







public function value() {}





public function isInfinite() {}





public function isFinite() {}





public function isNaN() {}







public function add($num) {}







public function sub($num) {}







public function mul($num) {}







public function div($num) {}







public function mod($num) {}





public function abs() {}





public function neg() {}





public function sqrt() {}





public function toInt() {}





public function toDouble() {}
}





final class Duration implements Value
{







public function __construct($months, $days, $nanos) {}







public function type() {}





public function months() {}





public function days() {}





public function nanos() {}





public function __toString() {}
}





final class DefaultKeyspace implements Keyspace
{






public function name() {}







public function replicationClassName() {}







public function replicationOptions() {}







public function hasDurableWrites() {}









public function table($name) {}







public function tables() {}









public function userType($name) {}







public function userTypes() {}









public function materializedView($name) {}







public function materializedViews() {}










public function function_($name, ...$params) {}







public function functions() {}










public function aggregate($name, ...$params) {}







public function aggregates() {}
}





final class Inet implements Value
{







public function __construct($address) {}







public function __toString() {}







public function type() {}







public function address() {}
}





final class Date implements Value
{







public function __construct($seconds) {}









public static function fromDateTime($datetime) {}







public function type() {}





public function seconds() {}









public function toDateTime($time) {}





public function __toString() {}
}





final class DefaultColumn implements Column
{






public function name() {}







public function type() {}







public function isReversed() {}







public function isStatic() {}







public function isFrozen() {}







public function indexName() {}







public function indexOptions() {}
}





final class Blob implements Value
{







public function __construct($bytes) {}







public function __toString() {}







public function type() {}







public function bytes() {}







public function toBinaryString() {}
}





final class DefaultTable implements Table
{






public function name() {}









public function option($name) {}







public function options() {}







public function comment() {}







public function readRepairChance() {}







public function localReadRepairChance() {}







public function gcGraceSeconds() {}







public function caching() {}







public function bloomFilterFPChance() {}







public function memtableFlushPeriodMs() {}







public function defaultTTL() {}







public function speculativeRetry() {}







public function indexInterval() {}







public function compactionStrategyClassName() {}







public function compactionStrategyOptions() {}







public function compressionParameters() {}







public function populateIOCacheOnFlush() {}







public function replicateOnWrite() {}







public function maxIndexInterval() {}







public function minIndexInterval() {}









public function column($name) {}







public function columns() {}







public function partitionKey() {}







public function primaryKey() {}







public function clusteringKey() {}





public function clusteringOrder() {}









public function index($name) {}







public function indexes() {}









public function materializedView($name) {}







public function materializedViews() {}
}





final class FutureValue implements Future
{











public function get($timeout) {}
}







final class Decimal implements Value, Numeric
{












public function __construct($value) {}







public function __toString() {}







public function type() {}







public function value() {}







public function scale() {}







public function add($num) {}







public function sub($num) {}







public function mul($num) {}







public function div($num) {}







public function mod($num) {}





public function abs() {}





public function neg() {}





public function sqrt() {}





public function toInt() {}





public function toDouble() {}
}







final class FutureClose implements Future
{











public function get($timeout) {}
}












final class PreparedStatement implements Statement
{
private function __construct() {}
}










#[Deprecated('Use an array of options instead of creating an instance of this class.')]
final class ExecutionOptions
{










public function __construct($options) {}







public function __get($name) {}
}





final class UserTypeValue implements Value, \Countable, \Iterator
{







public function __construct($types) {}







public function type() {}







public function values() {}









public function set($value) {}









public function get($name) {}







public function count() {}







public function current() {}







public function key() {}







public function next() {}







public function valid() {}







public function rewind() {}
}
}





namespace Cassandra\Cluster {






final class Builder
{






public function build() {}









public function withDefaultConsistency($consistency) {}










public function withDefaultPageSize($pageSize) {}










public function withDefaultTimeout($timeout) {}










public function withContactPoints(...$host) {}











public function withPort($port) {}







public function withRoundRobinLoadBalancingPolicy() {}











public function withDatacenterAwareRoundRobinLoadBalancingPolicy($localDatacenter, $hostPerRemoteDatacenter, $useRemoteDatacenterForLocalConsistencies) {}











public function withBlackListHosts($hosts) {}











public function withWhiteListHosts($hosts) {}












public function withBlackListDCs($dcs) {}











public function withWhiteListDCs($dcs) {}









public function withTokenAwareRouting($enabled) {}










public function withCredentials($username, $password) {}









public function withConnectTimeout($timeout) {}









public function withRequestTimeout($timeout) {}









public function withSSL($options) {}









public function withPersistentSessions($enabled) {}
















public function withProtocolVersion($version) {}












public function withIOThreads($count) {}















public function withConnectionsPerHost($core, $max) {}










public function withReconnectInterval($interval) {}









public function withLatencyAwareRouting($enabled) {}









public function withTCPNodelay($enabled) {}












public function withTCPKeepalive($delay) {}









public function withRetryPolicy($policy) {}










public function withTimestampGenerator($generator) {}














public function withSchemaMetadata($enabled) {}

















public function withHostnameResolution($enabled) {}















public function withRandomizedContactPoints($enabled) {}












public function withConnectionHeartbeatInterval($interval) {}
}
}





namespace Cassandra\TimestampGenerator {




final class ServerSide implements \Cassandra\TimestampGenerator {}











final class Monotonic implements \Cassandra\TimestampGenerator {}
}





namespace Cassandra\RetryPolicy {











final class DefaultPolicy implements \Cassandra\RetryPolicy {}























final class DowngradingConsistency implements \Cassandra\RetryPolicy {}





final class Fallthrough implements \Cassandra\RetryPolicy {}





final class Logging implements \Cassandra\RetryPolicy
{







public function __construct($childPolicy) {}
}
}





namespace Cassandra\Type {





final class Tuple extends \Cassandra\Type
{
private function __construct() {}







public function name() {}







public function __toString() {}







public function types() {}














public function create(...$values) {}
}






final class Collection extends \Cassandra\Type
{
private function __construct() {}







public function name() {}







public function valueType() {}







public function __toString() {}














public function create(...$value) {}
}






final class Set extends \Cassandra\Type
{
private function __construct() {}







public function name() {}







public function valueType() {}







public function __toString() {}













public function create(...$value) {}
}





final class Custom extends \Cassandra\Type
{
private function __construct() {}







public function name() {}







public function __toString() {}







public function create($value) {}
}






final class UserType extends \Cassandra\Type
{
private function __construct() {}









public function withName($name) {}







public function name() {}









public function withKeyspace($keyspace) {}







public function keyspace() {}








public function __toString() {}







public function types() {}














public function create(...$value) {}
}






final class Map extends \Cassandra\Type
{
private function __construct() {}







public function name() {}







public function keyType() {}







public function valueType() {}







public function __toString() {}





























public function create(...$value) {}
}





final class Scalar extends \Cassandra\Type
{
private function __construct() {}







public function name() {}







public function __toString() {}







public function create($value) {}
}
}





namespace Cassandra\SSLOptions {







final class Builder
{






public function build() {}











public function withTrustedCerts(...$path) {}











public function withVerifyFlags($flags) {}














public function withClientCert($path) {}













public function withPrivateKey($path, $passphrase) {}
}
}





namespace Cassandra\Exception {
use JetBrains\PhpStorm\Pure;







class ConfigurationException extends ValidationException
{







#[Pure]
public function __construct($message, $code, $previous) {}





public function __wakeup() {}





public function __toString() {}
}





class DomainException extends \DomainException implements \Cassandra\Exception
{







#[Pure]
public function __construct($message, $code, $previous) {}





public function __wakeup() {}





public function __toString() {}
}






class InvalidQueryException extends ValidationException
{







#[Pure]
public function __construct($message, $code, $previous) {}





public function __wakeup() {}





public function __toString() {}
}







class UnpreparedException extends ValidationException
{







#[Pure]
public function __construct($message, $code, $previous) {}





public function __wakeup() {}





public function __toString() {}
}





class InvalidArgumentException extends \InvalidArgumentException implements \Cassandra\Exception
{







#[Pure]
public function __construct($message, $code, $previous) {}





public function __wakeup() {}





public function __toString() {}
}







class ServerException extends RuntimeException
{







#[Pure]
public function __construct($message, $code, $previous) {}





public function __wakeup() {}





public function __toString() {}
}





class RangeException extends \RangeException implements \Cassandra\Exception
{







#[Pure]
public function __construct($message, $code, $previous) {}





public function __wakeup() {}





public function __toString() {}
}






class UnauthorizedException extends ValidationException
{







#[Pure]
public function __construct($message, $code, $previous) {}





public function __wakeup() {}





public function __toString() {}
}





class LogicException extends \LogicException implements \Cassandra\Exception
{







#[Pure]
public function __construct($message, $code, $previous) {}





public function __wakeup() {}





public function __toString() {}
}









class UnavailableException extends ExecutionException
{







#[Pure]
public function __construct($message, $code, $previous) {}





public function __wakeup() {}





public function __toString() {}
}






class AuthenticationException extends RuntimeException
{







#[Pure]
public function __construct($message, $code, $previous) {}





public function __wakeup() {}





public function __toString() {}
}





class OverloadedException extends ServerException
{







#[Pure]
public function __construct($message, $code, $previous) {}





public function __wakeup() {}





public function __toString() {}
}







class ReadTimeoutException extends ExecutionException
{







#[Pure]
public function __construct($message, $code, $previous) {}





public function __wakeup() {}





public function __toString() {}
}





class IsBootstrappingException extends ServerException
{







#[Pure]
public function __construct($message, $code, $previous) {}





public function __wakeup() {}





public function __toString() {}
}







class ProtocolException extends RuntimeException
{







#[Pure]
public function __construct($message, $code, $previous) {}





public function __wakeup() {}





public function __toString() {}
}









class ExecutionException extends RuntimeException
{







#[Pure]
public function __construct($message, $code, $previous) {}





public function __wakeup() {}





public function __toString() {}
}





class InvalidSyntaxException extends ValidationException
{







#[Pure]
public function __construct($message, $code, $previous) {}





public function __wakeup() {}





public function __toString() {}
}





class RuntimeException extends \RuntimeException implements \Cassandra\Exception
{







#[Pure]
public function __construct($message, $code, $previous) {}





public function __wakeup() {}





public function __toString() {}
}






class TimeoutException extends RuntimeException
{







#[Pure]
public function __construct($message, $code, $previous) {}





public function __wakeup() {}





public function __toString() {}
}












class ValidationException extends RuntimeException
{







#[Pure]
public function __construct($message, $code, $previous) {}





public function __wakeup() {}





public function __toString() {}
}






class TruncateException extends ExecutionException
{







#[Pure]
public function __construct($message, $code, $previous) {}





public function __wakeup() {}





public function __toString() {}
}





class AlreadyExistsException extends ConfigurationException
{







#[Pure]
public function __construct($message, $code, $previous) {}





public function __wakeup() {}





public function __toString() {}
}





class DivideByZeroException extends RangeException
{







#[Pure]
public function __construct($message, $code, $previous) {}





public function __wakeup() {}





public function __toString() {}
}







class WriteTimeoutException extends ExecutionException
{







#[Pure]
public function __construct($message, $code, $previous) {}





public function __wakeup() {}





public function __toString() {}
}
}
