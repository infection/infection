<?php








use JetBrains\PhpStorm\Deprecated;






class MongoClient
{
public const VERSION = '3.x';
public const DEFAULT_HOST = "localhost";
public const DEFAULT_PORT = 27017;
public const RP_PRIMARY = "primary";
public const RP_PRIMARY_PREFERRED = "primaryPreferred";
public const RP_SECONDARY = "secondary";
public const RP_SECONDARY_PREFERRED = "secondaryPreferred";
public const RP_NEAREST = "nearest";


public $connected = false;
public $status = null;
protected $server = null;
protected $persistent = null;








































































public function __construct($server = "mongodb://localhost:27017", array $options = ["connect" => true], $driver_options) {}














public function close($connection) {}









public function connect() {}








#[Deprecated(replacement: "%class%->drop()")]
public function dropDB($db) {}








public function __get($dbname) {}






public static function getConnections() {}








public function getHosts() {}






public function getReadPreference() {}








public function getWriteConcern() {}
















public function killCursor($server_hash, $id) {}







public function listDBs() {}










public function selectCollection($db, $collection) {}









public function selectDB($name) {}








public function setReadPreference($readPreference, $tags = null) {}









public function switchSlave() {}






public function __toString() {}
}








#[Deprecated("This class has been DEPRECATED as of version 1.3.0.")]
class Mongo extends MongoClient
{







#[Deprecated('This feature has been DEPRECATED as of version 1.2.3. Relying on this feature is highly discouraged. Please use MongoPool::getSize() instead.')]
public function getPoolSize() {}












public function getSlave() {}







public function getSlaveOkay() {}







#[Deprecated('Pass a string of the form "mongodb://server1,server2" to the constructor instead of using this method.')]
public function pairConnect() {}






















#[Deprecated('@deprecated This feature has been DEPRECATED as of version 1.2.3. Relying on this feature is highly discouraged. Please use MongoPool::info() instead.')]
public function poolDebug() {}











public function setSlaveOkay($ok) {}









#[Deprecated('Relying on this feature is highly discouraged. Please use MongoPool::setSize() instead.')]
public function setPoolSize($size) {}









#[Deprecated('Pass array("persist" => $id) to the constructor instead of using this method.')]
public function persistConnect($username = "", $password = "") {}









#[Deprecated('Pass "mongodb://server1,server2" and array("persist" => $id) to the constructor instead of using this method.')]
public function pairPersistConnect($username = "", $password = "") {}








protected function connectUtil() {}







#[Deprecated('Use MongoDB::lastError() instead.')]
public function lastError() {}







#[Deprecated('Use MongoDB::prevError() instead.')]
public function prevError() {}







#[Deprecated('Use MongoDB::resetError() instead.')]
public function resetError() {}







#[Deprecated('Use MongoDB::forceError() instead.')]
public function forceError() {}
}





class MongoDB
{




public const PROFILING_OFF = 0;





public const PROFILING_SLOW = 1;





public const PROFILING_ON = 2;


































public $w = 1;
















public $wtimeout = 10000;










public function __construct($conn, $name) {}






public function __toString() {}








public function __get($name) {}









public function getCollectionNames($includeSystemCollections = false) {}








public function getGridFS($prefix = "fs") {}







public function getProfilingLevel() {}







public function getSlaveOkay() {}








public function setProfilingLevel($level) {}







public function drop() {}








public function repair($preserve_cloned_files = false, $backup_original_files = false) {}











public function selectCollection($name) {}











public function setSlaveOkay($ok = true) {}



































public function createCollection($name, $options) {}









#[Deprecated('Use MongoCollection::drop() instead.')]
public function dropCollection($coll) {}








public function listCollections($includeSystemCollections = false) {}

















public function createDBRef($collection, $document_or_id) {}








public function getDBRef(array $ref) {}









public function getWriteConcern() {}









public function execute($code, array $args = []) {}


















public function command(array $data, $options) {}







public function lastError() {}







public function prevError() {}







public function resetError() {}







public function forceError() {}





































public function authenticate($username, $password) {}







public function getReadPreference() {}









public function setReadPreference($read_preference, array $tags) {}









public function setWriteConcern($w, $wtimeout) {}
}





class MongoCollection
{



public const ASCENDING = 1;




public const DESCENDING = -1;




public $db = null;









public $w;









public $wtimeout;








public function __construct(MongoDB $db, $name) {}






public function __toString() {}







public function __get($name) {}





















public function aggregate(array $pipeline, array $op, array $pipelineOperators) {}





















public function aggregateCursor(array $pipeline, array $options) {}






public function getName() {}










public function getSlaveOkay() {}















public function setSlaveOkay($ok = true) {}







public function getReadPreference() {}







public function setReadPreference($read_preference, array $tags) {}






public function drop() {}







public function validate($scan_data = false) {}















































public function insert($a, array $options = []) {}









public function batchInsert(array $a, array $options = []) {}






























public function update(array $criteria, array $newobj, array $options = []) {}










































public function remove(array $criteria = [], array $options = []) {}








public function find(array $query = [], array $fields = []) {}








public function distinct($key, array $query = null) {}










public function findAndModify(array $query, array $update = null, array $fields = null, array $options = null) {}









public function findOne(array $query = [], array $fields = [], array $options = []) {}








public function createIndex(array $keys, array $options = []) {}









#[Deprecated('Use MongoCollection::createIndex() instead.')]
public function ensureIndex(array $keys, array $options = []) {}







public function deleteIndex($keys) {}






public function deleteIndexes() {}






public function getIndexInfo() {}







public function count($query = []) {}
























public function save($a, array $options = []) {}







public function createDBRef(array $a) {}







public function getDBRef(array $ref) {}





protected static function toIndexString($keys) {}










public function group($keys, array $initial, MongoCode $reduce, array $condition = []) {}
}





class MongoCursor implements Iterator
{




public static $slaveOkay = false;











public static $timeout = 30000;









public function __construct($connection, $ns, array $query = [], array $fields = []) {}







public function awaitData($wait = true) {}








public function hasNext() {}








public function getNext() {}







public function getReadPreference() {}








public function limit($num) {}







public function partial($okay = true) {}













public function setFlag($flag, $set = true) {}








public function setReadPreference($read_preference, array $tags) {}








public function skip($num) {}









public function slaveOkay($okay = true) {}







public function tailable($tail = true) {}








public function immortal($liveForever = true) {}








public function timeout($ms) {}






public function dead() {}







public function snapshot() {}








public function sort(array $fields) {}








public function hint($key_pattern) {}









public function addOption($key, $value) {}







protected function doQuery() {}






public function current() {}






public function key() {}








public function next() {}







public function rewind() {}






public function valid() {}






public function reset() {}






public function explain() {}







public function count($all = false) {}








public function fields(array $f) {}






public function info() {}





































public function batchSize($batchSize) {}











public function maxTimeMS($ms) {}
}

class MongoCommandCursor implements MongoCursorInterface
{






public function current() {}







public function next() {}







public function key() {}








public function valid() {}







public function rewind() {}

public function batchSize(int $batchSize): MongoCursorInterface {}

public function dead(): bool {}

public function info(): array {}

public function getReadPreference(): array {}

public function setReadPreference(string $read_preference, array $tags = null): MongoCursorInterface {}

public function timeout(int $ms): MongoCursorInterface {}
}

interface MongoCursorInterface extends Iterator
{
public function batchSize(int $batchSize): MongoCursorInterface;

public function dead(): bool;

public function info(): array;

public function getReadPreference(): array;

public function setReadPreference(string $read_preference, array $tags = null): MongoCursorInterface;

public function timeout(int $ms): MongoCursorInterface;
}

class MongoGridFS extends MongoCollection
{
public const ASCENDING = 1;
public const DESCENDING = -1;





public $chunks;





protected $filesName;





protected $chunksName;











public function __construct($db, $prefix = "fs", $chunks = "fs") {}






public function drop() {}







public function find(array $query = [], array $fields = []) {}









public function storeFile($filename, $extra = [], $options = []) {}









public function storeBytes($bytes, $extra = [], $options = []) {}








public function findOne(array $query = [], array $fields = []) {}









public function remove(array $criteria = [], array $options = []) {}







public function delete($id) {}








public function storeUpload($name, array $metadata = []) {}







public function get($id) {}








public function put($filename, array $extra = []) {}
}

class MongoGridFSFile
{




public $file;





protected $gridfs;






public function __construct($gridfs, array $file) {}






public function getFilename() {}






public function getSize() {}







public function write($filename = null) {}






public function getBytes() {}









public function getResource() {}
}

class MongoGridFSCursor extends MongoCursor implements Traversable, Iterator
{



public static $slaveOkay;





protected $gridfs;










public function __construct($gridfs, $connection, $ns, $query, $fields) {}






public function getNext() {}






public function current() {}






public function key() {}
}





class MongoId
{




public $id = null;







public function __construct($id = null) {}












public static function isValid($value) {}







public function __toString() {}







public function getInc() {}







public function getPID() {}







public function getTimestamp() {}







public static function getHostname() {}








public static function __set_state(array $props) {}
}

class MongoCode
{



public $code;




public $scope;








public function __construct($code, array $scope = []) {}





public function __toString() {}
}

class MongoRegex
{




public $regex;





public $flags;







public function __construct($regex) {}





public function __toString() {}
}

class MongoDate
{




public $sec;





public $usec;








public function __construct($sec = 0, $usec = 0) {}






public function toDateTime() {}





public function __toString() {}
}

class MongoBinData
{




public const GENERIC = 0x0;





public const FUNC = 0x1;





public const BYTE_ARRAY = 0x2;





public const UUID = 0x3;





public const UUID_RFC4122 = 0x4;





public const MD5 = 0x5;





public const CUSTOM = 0x80;





public $bin;





public $type;








public function __construct($data, $type = 2) {}





public function __toString() {}
}

class MongoDBRef
{



protected static $refKey = '$ref';




protected static $idKey = '$id';










public static function create($collection, $id, $database = null) {}









public static function isRef($ref) {}








public static function get($db, $ref) {}
}

class MongoWriteBatch
{
public const COMMAND_INSERT = 1;
public const COMMAND_UPDATE = 2;
public const COMMAND_DELETE = 3;



























protected function __construct($collection, $batch_type, $write_options) {}

















































public function add(array $item) {}










final public function execute(array $write_options) {}
}

class MongoUpdateBatch extends MongoWriteBatch
{
















public function __construct(MongoCollection $collection, array $write_options) {}
}

class MongoException extends Exception {}

class MongoCursorException extends MongoException {}

class MongoCursorTimeoutException extends MongoCursorException {}

class MongoConnectionException extends MongoException {}

class MongoGridFSException extends MongoException {}





class MongoWriteConcernException extends MongoCursorException
{





public function getDocument() {}
}





class MongoExecutionTimeoutException extends MongoException {}




class MongoProtocolException extends MongoException {}





class MongoDuplicateKeyException extends MongoWriteConcernException {}





class MongoResultException extends MongoException
{






public function getDocument() {}
public $document;
}

class MongoTimestamp
{




public $sec;





public $inc;











public function __construct($sec = 0, $inc) {}




public function __toString() {}
}

class MongoInt32
{




public $value;







public function __construct($value) {}




public function __toString() {}
}

class MongoInt64
{




public $value;







public function __construct($value) {}




public function __toString() {}
}

class MongoLog
{



public const NONE = 0;




public const ALL = 0;




public const WARNING = 0;




public const INFO = 0;




public const FINE = 0;




public const RS = 0;




public const POOL = 0;




public const IO = 0;




public const SERVER = 0;




public const PARSE = 0;
public const CON = 2;


































public static function setCallback(callable $log_function) {}










public static function setLevel($level) {}








public static function getLevel() {}










public static function setModule($module) {}









public static function getModule() {}
}

class MongoPool
{

























public static function info() {}










public static function setSize($size) {}







public static function getSize() {}
}

class MongoMaxKey {}

class MongoMinKey {}
