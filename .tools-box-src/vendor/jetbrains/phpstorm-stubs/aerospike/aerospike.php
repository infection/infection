<?php
























use JetBrains\PhpStorm\Deprecated;
























































class Aerospike
{



















































































































public function __construct(array $config, bool $persistent_connection = true, array $options = []) {}







public function __destruct() {}
















public function isConnected() {}









public function close() {}




















public function reconnect() {}











public function shmKey() {}



















public function error() {}







public function errorno() {}




























































public function initKey(string $ns, string $set, $pk, bool $is_digest = false) {}



































public function getKeyDigest(string $ns, string $set, $pk) {}














































































































































public function put(array $key, array $bins, int $ttl = 0, array $options = []) {}







































































































































public function get(array $key, &$record, ?array $select = null, array $options = []) {}
























































public function exists(array $key, &$metadata, array $options = []) {}

















































public function touch(array $key, int $ttl = 0, array $options = []) {}







































public function remove(array $key, array $options = []) {}








































public function removeBin(array $key, array $bins, array $options = []) {}









































public function truncate(string $ns, string $set, int $nanos, array $options = []) {}









































public function increment(array $key, string $bin, $offset, array $options = []) {}









































public function append(array $key, string $bin, string $value, array $options = []) {}









































public function prepend(array $key, string $bin, string $value, array $options = []) {}














































































































































































































































































































































































































public function operate(array $key, array $operations, &$returned, array $options = []) {}


























































































































































































































































































































































































public function operateOrdered(array $key, array $operations, &$returned, array $options = []) {}
































public function listSize(array $key, $bin, &$count, array $options = []) {}
































public function listAppend(array $key, $bin, $value, array $options = []) {}
































public function listMerge(array $key, $bin, array $items, array $options = []) {}

































public function listInsert(array $key, $bin, $index, $value, array $options = []) {}

































public function listInsertItems(array $key, $bin, $index, array $elements, array $options = []) {}




































public function listPop(array $key, $bin, $index, &$element, array $options = []) {}





































public function listPopRange(array $key, $bin, $index, $count, &$elements, array $options = []) {}


































public function listRemove(array $key, $bin, $index, array $options = []) {}


































public function listRemoveRange(array $key, $bin, $index, $count, array $options = []) {}



































public function listTrim(array $key, $bin, $index, $count, array $options = []) {}

































public function listClear(array $key, $bin, array $options = []) {}

































public function listSet(array $key, $bin, $index, $value, array $options = []) {}

































public function listGet(array $key, $bin, $index, array &$element, array $options = []) {}


































public function listGetRange(array $key, $bin, $index, $count, &$elements, array $options = []) {}


























































































































































































































public function getMany(array $keys, &$records, array $select = [], array $options = []) {}


























































































































public function existsMany(array $keys, array &$metadata, array $options = []) {}














































public function scan(string $ns, string $set, callable $record_cb, array $select = [], array $options = []) {}






































































public function query(string $ns, string $set, array $where, callable $record_cb, array $select = [], array $options = []) {}
















public static function predicateEquals(string $bin, $val) {}

















public static function predicateBetween(string $bin, int $min, int $max) {}

























public static function predicateContains(string $bin, int $index_type, $val) {}


























public static function predicateRange(string $bin, int $index_type, int $min, int $max) {}
















public static function predicateGeoContainsGeoJSONPoint(string $bin, string $point) {}

















public static function predicateGeoContainsPoint(string $bin, float $long, float $lat) {}
















public static function predicateGeoWithinGeoJSONRegion(string $bin, string $region) {}


















public static function predicateGeoWithinRadius(string $bin, float $long, float $lat, float $radiusMeter) {}





























public function jobInfo(int $job_id, $job_type, array &$info, array $options = []) {}






























public function register($path, $module, $language = Aerospike::UDF_TYPE_LUA, $options = []) {}



















public function deregister($module, $options = []) {}













































public function listRegistered(&$modules, $language = Aerospike::UDF_TYPE_LUA, $options = []) {}














































public function getRegistered($module, &$code, $language = Aerospike::UDF_TYPE_LUA, $options = []) {}






















































public function apply(array $key, string $module, string $function, array $args = [], &$returned = null, $options = []) {}














































public function scanApply(string $ns, string $set, string $module, string $function, array $args, int &$job_id, array $options = []) {}




































































public function queryApply(string $ns, string $set, array $where, string $module, string $function, array $args, int &$job_id, array $options = []) {}










































































































































public function aggregate(string $ns, string $set, array $where, string $module, string $function, array $args, &$returned, array $options = []) {}




































public function addIndex(string $ns, string $set, string $bin, string $name, int $indexType, int $dataType, array $options = []) {}




















public function dropIndex(string $ns, string $name, array $options = []) {}





























public function info(string $request, string &$response, ?array $host = null, array $options = []) {}




































public function infoMany(string $request, ?array $host = null, array $options = []) {}


































public function getNodes() {}















public function setLogLevel(int $log_level) {}












































public function setLogHandler(callable $log_handler) {}






















public function setSerializer(callable $serialize_cb) {}





















public function setDeserializer(callable $unserialize_cb) {}










public const OPT_READ_DEFAULT_POL = "OPT_READ_DEFAULT_POL";




public const OPT_WRITE_DEFAULT_POL = "OPT_WRITE_DEFAULT_POL";




public const OPT_REMOVE_DEFAULT_POL = "OPT_REMOVE_DEFAULT_POL";




public const OPT_BATCH_DEFAULT_POL = "OPT_BATCH_DEFAULT_POL";




public const OPT_OPERATE_DEFAULT_POL = "OPT_OPERATE_DEFAULT_POL";




public const OPT_QUERY_DEFAULT_POL = "OPT_QUERY_DEFAULT_POL";




public const OPT_SCAN_DEFAULT_POL = "OPT_SCAN_DEFAULT_POL";




public const OPT_APPLY_DEFAULT_POL = "OPT_APPLY_DEFAULT_POL";





public const OPT_TLS_CONFIG = "OPT_TLS_CONFIG";



public const OPT_TLS_ENABLE = "OPT_TLS_ENABLE";




public const OPT_OPT_TLS_CAFILE = "OPT_OPT_TLS_CAFILE";




public const OPT_TLS_CAPATH = "OPT_TLS_CAPATH";



public const OPT_TLS_PROTOCOLS = "OPT_TLS_PROTOCOLS";




public const OPT_TLS_CIPHER_SUITE = "OPT_TLS_CIPHER_SUITE";




public const OPT_TLS_CRL_CHECK = "OPT_TLS_CRL_CHECK";




public const OPT_TLS_CRL_CHECK_ALL = "OPT_TLS_CRL_CHECK_ALL";


public const OPT_TLS_CERT_BLACKLIST = "OPT_TLS_CERT_BLACKLIST";


public const OPT_TLS_LOG_SESSION_INFO = "OPT_TLS_LOG_SESSION_INFO";




public const OPT_TLS_KEYFILE = "OPT_TLS_KEYFILE";



public const OPT_TLS_CERTFILE = "OPT_TLS_CERTFILE";

/**
@const

*/
public const OPT_CONNECT_TIMEOUT = "OPT_CONNECT_TIMEOUT";

/**
@const


*/
public const OPT_READ_TIMEOUT = "OPT_READ_TIMEOUT";

/**
@const


*/
public const OPT_WRITE_TIMEOUT = "OPT_WRITE_TIMEOUT";

/**
@const






*/
public const OPT_TTL = "OPT_TTL";

/**
@const





*/
public const OPT_POLICY_KEY = "OPT_POLICY_KEY";

/**
@const

*/
public const POLICY_KEY_DIGEST = 0;

/**
@const

*/
public const POLICY_KEY_SEND = 1;

/**
@const






*/
public const OPT_POLICY_EXISTS = "OPT_POLICY_EXISTS";

/**
@const


*/
public const POLICY_EXISTS_IGNORE = 0;

/**
@const

*/
public const POLICY_EXISTS_CREATE = 1;

/**
@const

*/
public const POLICY_EXISTS_UPDATE = 2;

/**
@const

*/
public const POLICY_EXISTS_REPLACE = 3;

/**
@const

*/
public const POLICY_EXISTS_CREATE_OR_REPLACE = 4;

/**
@const




*/
public const OPT_POLICY_GEN = "OPT_POLICY_GEN";

/**
@const

*/
public const POLICY_GEN_IGNORE = 0;

/**
@const

*/
public const POLICY_GEN_EQ = 1;

/**
@const
*/
public const POLICY_GEN_GT = 2;

/**
@const





*/
public const OPT_SERIALIZER = "OPT_SERIALIZER";

/**
@const

*/
public const SERIALIZER_NONE = 0;

/**
@const

*/
public const SERIALIZER_PHP = 1;

/**
@const

*/
public const SERIALIZER_USER = 2;

/**
@const






*/
public const OPT_POLICY_COMMIT_LEVEL = "OPT_POLICY_COMMIT_LEVEL";

/**
@const

*/
public const POLICY_COMMIT_LEVEL_ALL = 0;

/**
@const

*/
public const POLICY_COMMIT_LEVEL_MASTER = 1;

/**
@const




*/
public const OPT_POLICY_REPLICA = "OPT_POLICY_REPLICA";

/**
@const

*/
public const POLICY_REPLICA_MASTER = 0;

/**
@const

*/
public const POLICY_REPLICA_ANY = 1;

/**
@const



*/
public const POLICY_REPLICA_SEQUENCE = 2;

/**
@const





*/
public const POLICY_REPLICA_PREFER_RACK = 3;

/**
@const





*/
public const OPT_POLICY_READ_MODE_AP = "OPT_POLICY_READ_MODE_AP";

/**
@const

*/
public const POLICY_READ_MODE_AP_ONE = 0;

/**
@const

*/
public const AS_POLICY_READ_MODE_AP_ALL = 1;

/**
@const





*/
public const OPT_POLICY_READ_MODE_SC = "OPT_POLICY_READ_MODE_SC";

/**
@const

*/
public const POLICY_READ_MODE_SC_SESSION = 0;

/**
@const

*/
public const POLICY_READ_MODE_SC_LINEARIZE = 1;

/**
@const

*/
public const POLICY_READ_MODE_SC_ALLOW_REPLICA = 2;

/**
@const

*/
public const POLICY_READ_MODE_SC_ALLOW_UNAVAILABLE = 3;

/**
@const



*/
public const OPT_DESERIALIZE = "deserialize";





public const OPT_SLEEP_BETWEEN_RETRIES = "sleep_between_retries";











public const OPT_MAX_RETRIES = "OPT_MAX_RETRIES";











public const OPT_TOTAL_TIMEOUT = "OPT_TOTAL_TIMEOUT";












public const OPT_SOCKET_TIMEOUT = "OPT_SOCKET_TIMEOUT";




public const OPT_BATCH_CONCURRENT = "OPT_BATCH_CONCURRENT";














public const OPT_ALLOW_INLINE = "OPT_ALLOW_INLINE";







public const OPT_SEND_SET_NAME = "OPT_SEND_SET_NAME";




public const OPT_FAIL_ON_CLUSTER_CHANGE = "OPT_FAIL_ON_CLUSTER_CHANGE";

/**
@const


*/
public const OPT_SCAN_PRIORITY = "OPT_SCAN_PRIORITY";

/**
@const

*/
public const SCAN_PRIORITY_AUTO = "SCAN_PRIORITY_AUTO";

/**
@const

*/
public const SCAN_PRIORITY_LOW = "SCAN_PRIORITY_LOW";

/**
@const

*/
public const SCAN_PRIORITY_MEDIUM = "SCAN_PRIORITY_MEDIUM";

/**
@const

*/
public const SCAN_PRIORITY_HIGH = "SCAN_PRIORITY_HIGH";

/**
@const


*/
public const OPT_SCAN_NOBINS = "OPT_SCAN_NOBINS";

/**
@const


*/
public const OPT_SCAN_PERCENTAGE = "OPT_SCAN_PERCENTAGE";

/**
@const


*/
public const OPT_SCAN_CONCURRENTLY = "OPT_SCAN_CONCURRENTLY";

/**
@const


*/
public const OPT_QUERY_NOBINS = "OPT_QUERY_NOBINS";

/**
@const


*/
public const USE_BATCH_DIRECT = "USE_BATCH_DIRECT";

/**
@const



*/
public const OPT_POLICY_DURABLE_DELETE = "OPT_POLICY_DURABLE_DELETE";

/**
@const





*/
public const OPT_MAP_ORDER = "OPT_MAP_ORDER";

/**
@const

*/
public const AS_MAP_UNORDERED = "AS_MAP_UNORDERED";

/**
@const

*/
public const AS_MAP_KEY_ORDERED = "AS_MAP_KEY_ORDERED";

/**
@const

*/
public const AS_MAP_KEY_VALUE_ORDERED = "AS_MAP_KEY_VALUE_ORDERED";

/**
@const




*/
public const OPT_MAP_WRITE_MODE = "OPT_MAP_WRITE_MODE";

/**
@const
*/
public const AS_MAP_UPDATE = "AS_MAP_UPDATE";

/**
@const
*/
public const AS_MAP_UPDATE_ONLY = "AS_MAP_UPDATE_ONLY";

/**
@const
*/
public const AS_MAP_CREATE_ONLY = "AS_MAP_CREATE_ONLY";

/**
@const






*/
public const OPT_MAP_WRITE_FLAGS = "OPT_MAP_WRITE_FLAGS";

/**
@const

*/
public const AS_MAP_WRITE_DEFAULT = "AS_MAP_WRITE_DEFAULT";

/**
@const

*/
public const AS_MAP_WRITE_CREATE_ONLY = "AS_MAP_WRITE_CREATE_ONLY";

/**
@const

*/
public const AS_MAP_WRITE_UPDATE_ONLY = "AS_MAP_WRITE_UPDATE_ONLY";

/**
@const

*/
public const AS_MAP_WRITE_NO_FAIL = "AS_MAP_WRITE_NO_FAIL";

/**
@const

*/
public const AS_MAP_WRITE_PARTIAL = "AS_MAP_WRITE_PARTIAL";

/**
@const


*/
public const MAP_RETURN_NONE = "AS_MAP_RETURN_NONE";

/**
@const


*/
public const MAP_RETURN_INDEX = "AS_MAP_RETURN_INDEX";

/**
@const


*/
public const MAP_RETURN_REVERSE_INDEX = "AS_MAP_RETURN_REVERSE_INDEX";

/**
@const


*/
public const MAP_RETURN_RANK = "AS_MAP_RETURN_RANK";

/**
@const


*/
public const MAP_RETURN_REVERSE_RANK = "AS_MAP_RETURN_REVERSE_RANK";

/**
@const


*/
public const MAP_RETURN_COUNT = "AS_MAP_RETURN_COUNT";

/**
@const


*/
public const MAP_RETURN_KEY = "AS_MAP_RETURN_KEY";

/**
@const


*/
public const MAP_RETURN_VALUE = "AS_MAP_RETURN_VALUE";

/**
@const



*/
public const MAP_RETURN_KEY_VALUE = "AS_MAP_RETURN_KEY_VALUE";

/**
@const
*/
public const LOG_LEVEL_OFF = "LOG_LEVEL_OFF";

/**
@const
*/
public const LOG_LEVEL_ERROR = "LOG_LEVEL_ERROR";

/**
@const
*/
public const LOG_LEVEL_WARN = "LOG_LEVEL_WARN";

/**
@const
*/
public const LOG_LEVEL_INFO = "LOG_LEVEL_INFO";

/**
@const
*/
public const LOG_LEVEL_DEBUG = "LOG_LEVEL_DEBUG";

/**
@const
*/
public const LOG_LEVEL_TRACE = "LOG_LEVEL_TRACE";

/**
@const









*/
public const OK = "AEROSPIKE_OK";



/**
@const

*/
public const ERR_CONNECTION = "AEROSPIKE_ERR_CONNECTION";

/**
@const

*/
public const ERR_TLS_ERROR = "AEROSPIKE_ERR_TLS";

/**
@const

*/
public const ERR_INVALID_NODE = "AEROSPIKE_ERR_INVALID_NODE";

/**
@const

*/
public const ERR_NO_MORE_CONNECTIONS = "AEROSPIKE_ERR_NO_MORE_CONNECTIONS";

/**
@const

*/
public const ERR_ASYNC_CONNECTION = "AEROSPIKE_ERR_ASYNC_CONNECTION";

/**
@const

*/
public const ERR_CLIENT_ABORT = "AEROSPIKE_ERR_CLIENT_ABORT";

/**
@const

*/
public const ERR_INVALID_HOST = "AEROSPIKE_ERR_INVALID_HOST";

/**
@const

*/
public const ERR_PARAM = "AEROSPIKE_ERR_PARAM";

/**
@const

*/
public const ERR_CLIENT = "AEROSPIKE_ERR_CLIENT";



/**
@const

*/
public const ERR_SERVER = "AEROSPIKE_ERR_SERVER";

/**
@const



*/
public const ERR_RECORD_NOT_FOUND = "AEROSPIKE_ERR_RECORD_NOT_FOUND";

/**
@const

*/
public const ERR_RECORD_GENERATION = "AEROSPIKE_ERR_RECORD_GENERATION";

/**
@const


*/
public const ERR_REQUEST_INVALID = "AEROSPIKE_ERR_REQUEST_INVALID";

/**
@const

*/
public const ERR_OP_NOT_APPLICABLE = "AEROSPIKE_ERR_OP_NOT_APPLICABLE";

/**
@const


*/
public const ERR_RECORD_EXISTS = "AEROSPIKE_ERR_RECORD_EXISTS";

/**
@const


*/
public const ERR_BIN_EXISTS = "AEROSPIKE_ERR_BIN_EXISTS";

/**
@const


*/
public const ERR_CLUSTER_CHANGE = "AEROSPIKE_ERR_CLUSTER_CHANGE";

/**
@const




*/
public const ERR_SERVER_FULL = "AEROSPIKE_ERR_SERVER_FULL";

/**
@const

*/
public const ERR_TIMEOUT = "AEROSPIKE_ERR_TIMEOUT";

/**
@const


*/
#[Deprecated("Will be reused as ERR_ALWAYS_FORBIDDEN")]
public const ERR_ALWAYS_FORBIDDEN = "AEROSPIKE_ERR_ALWAYS_FORBIDDEN";

/**
@const


*/
public const ERR_CLUSTER = "AEROSPIKE_ERR_CLUSTER";

/**
@const



*/
public const ERR_BIN_INCOMPATIBLE_TYPE = "AEROSPIKE_ERR_BIN_INCOMPATIBLE_TYPE";

/**
@const

*/
public const ERR_RECORD_TOO_BIG = "AEROSPIKE_ERR_RECORD_TOO_BIG";

/**
@const


*/
public const ERR_RECORD_BUSY = "AEROSPIKE_ERR_RECORD_BUSY";

/**
@const

*/
public const ERR_SCAN_ABORTED = "AEROSPIKE_ERR_SCAN_ABORTED";

/**
@const


*/
public const ERR_UNSUPPORTED_FEATURE = "AEROSPIKE_ERR_UNSUPPORTED_FEATURE";

/**
@const


*/
public const ERR_BIN_NOT_FOUND = "AEROSPIKE_ERR_BIN_NOT_FOUND";

/**
@const

*/
public const ERR_DEVICE_OVERLOAD = "AEROSPIKE_ERR_DEVICE_OVERLOAD";

/**
@const


*/
public const ERR_RECORD_KEY_MISMATCH = "AEROSPIKE_ERR_RECORD_KEY_MISMATCH";

/**
@const

*/
public const ERR_NAMESPACE_NOT_FOUND = "AEROSPIKE_ERR_NAMESPACE_NOT_FOUND";

/**
@const

*/
public const ERR_BIN_NAME = "AEROSPIKE_ERR_BIN_NAME";

/**
@const




*/
public const ERR_FAIL_FORBIDDEN = "AEROSPIKE_ERR_FORBIDDEN";

/**
@const

*/
public const ERR_FAIL_ELEMENT_NOT_FOUND = "AEROSPIKE_ERR_FAIL_NOT_FOUND";

/**
@const

*/
public const ERR_FAIL_ELEMENT_EXISTS = "AEROSPIKE_ERR_FAIL_ELEMENT_EXISTS";



/**
@const

*/
public const ERR_SECURITY_NOT_SUPPORTED = "AEROSPIKE_ERR_SECURITY_NOT_SUPPORTED";

/**
@const

*/
public const ERR_SECURITY_NOT_ENABLED = "AEROSPIKE_ERR_SECURITY_NOT_ENABLED";

/**
@const

*/
public const ERR_SECURITY_SCHEME_NOT_SUPPORTED = "AEROSPIKE_ERR_SECURITY_SCHEME_NOT_SUPPORTED";

/**
@const

*/
public const ERR_INVALID_COMMAND = "AEROSPIKE_ERR_INVALID_COMMAND";

/**
@const

*/
public const ERR_INVALID_FIELD = "AEROSPIKE_ERR_INVALID_FIELD";

/**
@const

*/
public const ERR_ILLEGAL_STATE = "AEROSPIKE_ERR_ILLEGAL_STATE";

/**
@const

*/
public const ERR_INVALID_USER = "AEROSPIKE_ERR_INVALID_USER";

/**
@const

*/
public const ERR_USER_ALREADY_EXISTS = "AEROSPIKE_ERR_USER_ALREADY_EXISTS";

/**
@const

*/
public const ERR_INVALID_PASSWORD = "AEROSPIKE_ERR_INVALID_PASSWORD";

/**
@const

*/
public const ERR_EXPIRED_PASSWORD = "AEROSPIKE_ERR_EXPIRED_PASSWORD";

/**
@const

*/
public const ERR_FORBIDDEN_PASSWORD = "AEROSPIKE_ERR_FORBIDDEN_PASSWORD";

/**
@const

*/
public const ERR_INVALID_CREDENTIAL = "AEROSPIKE_ERR_INVALID_CREDENTIAL";

/**
@const

*/
public const ERR_INVALID_ROLE = "AEROSPIKE_ERR_INVALID_ROLE";

/**
@const

*/
public const ERR_INVALID_PRIVILEGE = "AEROSPIKE_ERR_INVALID_PRIVILEGE";

/**
@const

*/
public const ERR_NOT_AUTHENTICATED = "AEROSPIKE_ERR_NOT_AUTHENTICATED";

/**
@const

*/
public const ERR_ROLE_VIOLATION = "AEROSPIKE_ERR_ROLE_VIOLATION";

/**
@const

*/
public const ERR_ROLE_ALREADY_EXISTS = "AEROSPIKE_ERR_ROLE_ALREADY_EXISTS";



/**
@const

*/
public const ERR_UDF = "AEROSPIKE_ERR_UDF";

/**
@const

*/
public const ERR_UDF_NOT_FOUND = "AEROSPIKE_ERR_UDF_NOT_FOUND";

/**
@const

*/
public const ERR_LUA_FILE_NOT_FOUND = "AEROSPIKE_ERR_LUA_FILE_NOT_FOUND";



/**
@const

*/
public const ERR_BATCH_DISABLED = "AEROSPIKE_ERR_BATCH_DISABLED";

/**
@const

*/
public const ERR_BATCH_MAX_REQUESTS_EXCEEDED = "AEROSPIKE_ERR_BATCH_MAX_REQUESTS_EXCEEDED";

/**
@const

*/
public const ERR_BATCH_QUEUES_FULL = "AEROSPIKE_ERR_BATCH_QUEUES_FULL";



/**
@const

*/
public const ERR_GEO_INVALID_GEOJSON = "AEROSPIKE_ERR_GEO_INVALID_GEOJSON";



/**
@const
@const






*/
public const ERR_INDEX_FOUND = "AEROSPIKE_ERR_INDEX_FOUND";

/**
@const

*/
public const ERR_INDEX_NOT_FOUND = "AEROSPIKE_ERR_INDEX_NOT_FOUND";

/**
@const

*/
public const ERR_INDEX_OOM = "AEROSPIKE_ERR_INDEX_OOM";

/**
@const

*/
public const ERR_INDEX_NOT_READABLE = "AEROSPIKE_ERR_INDEX_NOT_READABLE";

/**
@const

*/
public const ERR_INDEX = "AEROSPIKE_ERR_INDEX";

/**
@const

*/
public const ERR_INDEX_NAME_MAXLEN = "AEROSPIKE_ERR_INDEX_NAME_MAXLEN";

/**
@const

*/
public const ERR_INDEX_MAXCOUNT = "AEROSPIKE_ERR_INDEX_MAXCOUNT";

/**
@const

*/
public const ERR_QUERY_ABORTED = "AEROSPIKE_ERR_QUERY_ABORTED";

/**
@const

*/
public const ERR_QUERY_QUEUE_FULL = "AEROSPIKE_ERR_QUERY_QUEUE_FULL";

/**
@const

*/
public const ERR_QUERY_TIMEOUT = "AEROSPIKE_ERR_QUERY_TIMEOUT";

/**
@const

*/
public const ERR_QUERY = "AEROSPIKE_ERR_QUERY";

/**
@const

*/
public const OPERATOR_WRITE = "OPERATOR_WRITE";

/**
@const

*/
public const OPERATOR_READ = "OPERATOR_READ";

/**
@const

*/
public const OPERATOR_INCR = "OPERATOR_INCR";

/**
@const

*/
public const OPERATOR_PREPEND = "OPERATOR_PREPEND";

/**
@const

*/
public const OPERATOR_APPEND = "OPERATOR_APPEND";

/**
@const

*/
public const OPERATOR_TOUCH = "OPERATOR_TOUCH";

/**
@const

*/
public const OPERATOR_DELETE = "OPERATOR_DELETE";



/**
@const

*/
public const OP_LIST_APPEND = "OP_LIST_APPEND";

/**
@const

*/
public const OP_LIST_MERGE = "OP_LIST_MERGE";

/**
@const

*/
public const OP_LIST_INSERT = "OP_LIST_INSERT";

/**
@const

*/
public const OP_LIST_INSERT_ITEMS = "OP_LIST_INSERT_ITEMS";

/**
@const

*/
public const OP_LIST_POP = "OP_LIST_POP";

/**
@const

*/
public const OP_LIST_POP_RANGE = "OP_LIST_POP_RANGE";

/**
@const

*/
public const OP_LIST_REMOVE = "OP_LIST_REMOVE";

/**
@const

*/
public const OP_LIST_REMOVE_RANGE = "OP_LIST_REMOVE_RANGE";

/**
@const

*/
public const OP_LIST_CLEAR = "OP_LIST_CLEAR";

/**
@const

*/
public const OP_LIST_SET = "OP_LIST_SET";

/**
@const

*/
public const OP_LIST_GET = "OP_LIST_GET";

/**
@const

*/
public const OP_LIST_GET_RANGE = "OP_LIST_GET_RANGE";

/**
@const

*/
public const OP_LIST_TRIM = "OP_LIST_TRIM";

/**
@const

*/
public const OP_LIST_SIZE = "OP_LIST_SIZE";



/**
@const

*/
public const OP_MAP_SIZE = "OP_MAP_SIZE";

/**
@const

*/
public const OP_MAP_CLEAR = "OP_MAP_CLEAR";

/**
@const

*/
public const OP_MAP_SET_POLICY = "OP_MAP_SET_POLICY";

/**
@const

*/
public const OP_MAP_GET_BY_KEY = "OP_MAP_GET_BY_KEY";

/**
@const

*/
public const OP_MAP_GET_BY_KEY_RANGE = "OP_MAP_GET_BY_KEY_RANGE";

/**
@const

*/
public const OP_MAP_GET_BY_VALUE = "OP_MAP_GET_BY_VALUE";

/**
@const

*/
public const OP_MAP_GET_BY_VALUE_RANGE = "OP_MAP_GET_BY_VALUE_RANGE";

/**
@const

*/
public const OP_MAP_GET_BY_INDEX = "OP_MAP_GET_BY_INDEX";

/**
@const

*/
public const OP_MAP_GET_BY_INDEX_RANGE = "OP_MAP_GET_BY_INDEX_RANGE";

/**
@const

*/
public const OP_MAP_GET_BY_RANK = "OP_MAP_GET_BY_RANK";

/**
@const

*/
public const OP_MAP_GET_BY_RANK_RANGE = "OP_MAP_GET_BY_RANK_RANGE";

/**
@const

*/
public const OP_MAP_PUT = "OP_MAP_PUT";

/**
@const

*/
public const OP_MAP_PUT_ITEMS = "OP_MAP_PUT_ITEMS";

/**
@const

*/
public const OP_MAP_INCREMENT = "OP_MAP_INCREMENT";

/**
@const

*/
public const OP_MAP_DECREMENT = "OP_MAP_DECREMENT";

/**
@const

*/
public const OP_MAP_REMOVE_BY_KEY = "OP_MAP_REMOVE_BY_KEY";

/**
@const

*/
public const OP_MAP_REMOVE_BY_KEY_LIST = "OP_MAP_REMOVE_BY_KEY_LIST";

/**
@const

*/
public const OP_MAP_REMOVE_BY_KEY_RANGE = "OP_MAP_REMOVE_BY_KEY_RANGE";

/**
@const

*/
public const OP_MAP_REMOVE_BY_VALUE = "OP_MAP_REMOVE_BY_VALUE";

/**
@const

*/
public const OP_MAP_REMOVE_BY_VALUE_RANGE = "OP_MAP_REMOVE_BY_VALUE_RANGE";

/**
@const

*/
public const OP_MAP_REMOVE_BY_VALUE_LIST = "OP_MAP_REMOVE_BY_VALUE_LIST";

/**
@const

*/
public const OP_MAP_REMOVE_BY_INDEX = "OP_MAP_REMOVE_BY_INDEX";

/**
@const

*/
public const OP_MAP_REMOVE_BY_INDEX_RANGE = "OP_MAP_REMOVE_BY_INDEX_RANGE";

/**
@const

*/
public const OP_MAP_REMOVE_BY_RANK = "OP_MAP_REMOVE_BY_RANK";

/**
@const

*/
public const OP_MAP_REMOVE_BY_RANK_RANGE = "OP_MAP_REMOVE_BY_RANK_RANGE";



/**
@const


*/
public const OP_EQ = "=";

/**
@const


*/
public const OP_BETWEEN = "BETWEEN";

/**
@const


*/
public const OP_CONTAINS = "CONTAINS";

/**
@const


*/
public const OP_RANGE = "RANGE";

/**
@const

*/
public const OP_GEOWITHINREGION = "GEOWITHIN";

/**
@const

*/
public const OP_GEOCONTAINSPOINT = "GEOCONTAINS";




#[Deprecated('use JOB_STATUS_UNDEF along with jobInfo()')]
public const SCAN_STATUS_UNDEF = "SCAN_STATUS_UNDEF";




#[Deprecated('use JOB_STATUS_INPROGRESS along with jobInfo()')]
public const SCAN_STATUS_INPROGRESS = "SCAN_STATUS_INPROGRESS";




#[Deprecated]
public const SCAN_STATUS_ABORTED = "SCAN_STATUS_ABORTED";




#[Deprecated('use JOB_STATUS_COMPLETED along with jobInfo()')]
public const SCAN_STATUS_COMPLETED = "SCAN_STATUS_COMPLETED";






public const JOB_STATUS_UNDEF = "JOB_STATUS_UNDEF";




public const JOB_STATUS_INPROGRESS = "JOB_STATUS_INPROGRESS";




public const JOB_STATUS_COMPLETED = "JOB_STATUS_COMPLETED";


/**
@const

*/
public const INDEX_TYPE_DEFAULT = "INDEX_TYPE_DEFAULT";

/**
@const

*/
public const INDEX_TYPE_LIST = "INDEX_TYPE_LIST";

/**
@const

*/
public const INDEX_TYPE_MAPKEYS = "INDEX_TYPE_MAPKEYS";

/**
@const

*/
public const INDEX_TYPE_MAPVALUES = "INDEX_TYPE_MAPVALUES";


/**
@const

*/
public const INDEX_STRING = "INDEX_STRING";

/**
@const

*/
public const INDEX_NUMERIC = "INDEX_NUMERIC";

/**
@const

*/
public const INDEX_GEO2DSPHERE = "INDEX_GEO2DSPHERE";

/**
@const

*/
public const UDF_TYPE_LUA = "UDF_TYPE_LUA";



/**
@const


*/
public const PRIV_READ = "PRIV_READ";

/**
@const


*/
public const PRIV_READ_WRITE = "PRIV_READ_WRITE";

/**
@const


*/
public const PRIV_READ_WRITE_UDF = "PRIV_READ_WRITE_UDF";

/**
@const


*/
public const PRIV_USER_ADMIN = "PRIV_USER_ADMIN";

/**
@const


*/
public const PRIV_DATA_ADMIN = "PRIV_DATA_ADMIN"; 

/**
@const

*/
public const PRIV_SYS_ADMIN = "PRIV_SYS_ADMIN"; 




















}
