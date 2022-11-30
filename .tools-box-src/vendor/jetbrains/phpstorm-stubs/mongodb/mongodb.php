<?php













namespace MongoDB {}

namespace MongoDB\Driver {
use MongoDB\BSON\Serializable;
use MongoDB\Driver\Exception\AuthenticationException;
use MongoDB\Driver\Exception\BulkWriteException;
use MongoDB\Driver\Exception\CommandException;
use MongoDB\Driver\Exception\ConnectionException;
use MongoDB\Driver\Exception\EncryptionException;
use MongoDB\Driver\Exception\Exception;
use MongoDB\Driver\Exception\InvalidArgumentException;
use MongoDB\Driver\Exception\RuntimeException;
use MongoDB\Driver\Exception\UnexpectedValueException;
use MongoDB\Driver\Exception\WriteConcernException;
use MongoDB\Driver\Exception\WriteException;
use MongoDB\Driver\Monitoring\Subscriber;
use Traversable;







final class Manager
{









final public function __construct($uri = '', array $options = [], array $driverOptions = []) {}

final public function __wakeup() {}









final public function createClientEncryption(array $options) {}















final public function executeBulkWrite($namespace, BulkWrite $zbulk, $options = []) {}















final public function executeCommand($db, Command $command, $options = []) {}














final public function executeQuery($namespace, Query $zquery, $options = []) {}















final public function executeReadCommand($db, Command $command, array $options = []) {}















final public function executeReadWriteCommand($db, Command $command, $options = []) {}















final public function executeWriteCommand($db, Command $command, array $options = []) {}







final public function getReadConcern() {}







final public function getReadPreference() {}







final public function getServers() {}







final public function getWriteConcern() {}











final public function selectServer(ReadPreference $readPreference = null) {}










final public function startSession(?array $options = []) {}

final public function addSubscriber(Subscriber $subscriber) {}

final public function removeSubscriber(Subscriber $subscriber) {}
}




final class Server
{
public const TYPE_UNKNOWN = 0;
public const TYPE_STANDALONE = 1;
public const TYPE_MONGOS = 2;
public const TYPE_POSSIBLE_PRIMARY = 3;
public const TYPE_RS_PRIMARY = 4;
public const TYPE_RS_SECONDARY = 5;
public const TYPE_RS_ARBITER = 6;
public const TYPE_RS_OTHER = 7;
public const TYPE_RS_GHOST = 8;






final private function __construct() {}

final public function __wakeup() {}















final public function executeBulkWrite($namespace, BulkWrite $zbulk, $options = []) {}














final public function executeCommand($db, Command $command, $options = null) {}














final public function executeReadCommand($db, Command $command, array $options = []) {}














final public function executeReadWriteCommand($db, Command $command, array $options = []) {}














final public function executeWriteCommand($db, Command $command, array $options = []) {}
















































final public function executeQuery($namespace, Query $zquery, $options = []) {}







final public function getHost() {}







final public function getInfo() {}







final public function getLatency() {}







final public function getPort() {}







final public function getTags() {}







final public function getType() {}







final public function isArbiter() {}







final public function isHidden() {}







final public function isPassive() {}







final public function isPrimary() {}







final public function isSecondary() {}
}





final class Query
{







final public function __construct($filter, ?array $options = []) {}

final public function __wakeup() {}
}







final class Command
{








final public function __construct($document, ?array $options = []) {}

final public function __wakeup() {}
}





final class ReadPreference implements Serializable, \Serializable
{
public const RP_PRIMARY = 1;
public const RP_PRIMARY_PREFERRED = 5;
public const RP_SECONDARY = 2;
public const RP_SECONDARY_PREFERRED = 6;
public const RP_NEAREST = 10;




public const PRIMARY = 'primary';




public const PRIMARY_PREFERRED = 'primaryPreferred';




public const SECONDARY = 'secondary';




public const SECONDARY_PREFERRED = 'secondaryPreferred';




public const NEAREST = 'nearest';




public const NO_MAX_STALENESS = -1;




public const SMALLEST_MAX_STALENESS_SECONDS = 90;









final public function __construct($mode, ?array $tagSets = null, ?array $options = []) {}

public static function __set_state(array $properties) {}







final public function getHedge() {}






final public function getMode() {}








final public function getModeString() {}






final public function getTagSets() {}








final public function bsonSerialize() {}








final public function serialize() {}










final public function unserialize($serialized) {}

final public function getMaxStalenessSeconds() {}
}






final class ReadConcern implements Serializable, \Serializable
{



public const LINEARIZABLE = 'linearizable';
public const LOCAL = 'local';
public const MAJORITY = 'majority';




public const AVAILABLE = 'available';






final public function __construct($level = null) {}

public static function __set_state(array $properties) {}







final public function getLevel() {}







final public function bsonSerialize() {}








final public function isDefault() {}








final public function serialize() {}










final public function unserialize($serialized) {}
}





final class Cursor implements CursorInterface, \Iterator
{





final private function __construct() {}

final public function __wakeup() {}






public function current() {}







final public function getId() {}







final public function getServer() {}







final public function isDead() {}






public function key() {}









public function next() {}










public function rewind() {}











final public function setTypeMap(array $typemap) {}







final public function toArray() {}






public function valid() {}
}





final class CursorId implements \Serializable
{






final private function __construct() {}







final public function __toString() {}

final public function __wakeup() {}

public static function __set_state(array $properties) {}








final public function serialize() {}










final public function unserialize($serialized) {}
}










final class BulkWrite implements \Countable
{







final public function __construct(?array $options = []) {}

final public function __wakeup() {}








final public function count() {}








final public function delete($query, ?array $deleteOptions = []) {}









final public function insert($document) {}









final public function update($query, $newObj, ?array $updateOptions = []) {}
}




final class WriteConcern implements Serializable, \Serializable
{



public const MAJORITY = 'majority';









final public function __construct($w, $wtimeout = 0, $journal = false) {}

public static function __set_state(array $properties) {}






final public function getJournal() {}






final public function getW() {}






final public function getWtimeout() {}








final public function bsonSerialize() {}








final public function serialize() {}










final public function unserialize($serialized) {}

final public function isDefault() {}
}





final class WriteResult
{
final private function __construct() {}

final public function __wakeup() {}






final public function getDeletedCount() {}






final public function getInsertedCount() {}






final public function getMatchedCount() {}






final public function getModifiedCount() {}






final public function getServer() {}






final public function getUpsertedCount() {}






final public function getUpsertedIds() {}






final public function getWriteConcernError() {}






final public function getWriteErrors() {}






final public function isAcknowledged() {}
}




final class WriteError
{
final private function __construct() {}

final public function __wakeup() {}






final public function getCode() {}






final public function getIndex() {}






final public function getInfo() {}






final public function getMessage() {}
}





final class WriteConcernError
{
final private function __construct() {}

final public function __wakeup() {}






final public function getCode() {}






final public function getInfo() {}






final public function getMessage() {}
}







final class Session
{



public const TRANSACTION_NONE = 'none';




public const TRANSACTION_STARTING = 'starting';




public const TRANSACTION_IN_PROGRESS = 'in_progress';




public const TRANSACTION_COMMITTED = 'committed';




public const TRANSACTION_ABORTED = 'aborted';






final private function __construct() {}

final public function __wakeup() {}







final public function abortTransaction() {}









final public function advanceClusterTime($clusterTime) {}









final public function advanceOperationTime($timestamp) {}













final public function commitTransaction() {}










final public function endSession() {}








final public function getClusterTime() {}








final public function getLogicalSessionId() {}








final public function getOperationTime() {}








final public function getServer() {}








final public function getTransactionOptions() {}








final public function getTransactionState() {}








final public function isInTransaction() {}











final public function startTransaction(?array $options = []) {}
}






interface CursorInterface extends Traversable
{






public function getId();








public function getServer();







public function isDead();








public function setTypeMap(array $typemap);







public function toArray();
}






final class ClientEncryption
{
public const AEAD_AES_256_CBC_HMAC_SHA_512_DETERMINISTIC = 'AEAD_AES_256_CBC_HMAC_SHA_512-Deterministic';
public const AEAD_AES_256_CBC_HMAC_SHA_512_RANDOM = 'AEAD_AES_256_CBC_HMAC_SHA_512-Random';

final private function __construct() {}

final public function __wakeup() {}










final public function createDataKey($kmsProvider, ?array $options = []) {}









final public function decrypt(\MongoDB\BSON\BinaryInterface $keyVaultClient) {}










final public function encrypt($value, ?array $options = []) {}
}
}

namespace MongoDB\Driver\Exception {
use MongoDB\Driver\WriteResult;
use Throwable;






class RuntimeException extends \RuntimeException implements Exception
{




protected $errorLabels;








final public function hasErrorLabel($label) {}
}





interface Exception extends Throwable {}






class AuthenticationException extends ConnectionException implements Exception {}






class ConnectionException extends RuntimeException implements Exception {}






class InvalidArgumentException extends \InvalidArgumentException implements Exception {}







class CommandException extends ServerException
{
protected $resultDocument;







final public function getResultDocument() {}
}








class ServerException extends RuntimeException implements Exception {}







abstract class WriteException extends ServerException implements Exception
{



protected $writeResult;





final public function getWriteResult() {}
}

class WriteConcernException extends RuntimeException implements Exception {}






class UnexpectedValueException extends \UnexpectedValueException implements Exception {}






class BulkWriteException extends WriteException implements Exception {}





class ConnectionTimeoutException extends ConnectionException implements Exception {}





class ExecutionTimeoutException extends ServerException implements Exception {}





class LogicException extends \LogicException implements Exception {}





class SSLConnectionException extends ConnectionException implements Exception {}






class EncryptionException extends RuntimeException implements Exception {}
}





namespace MongoDB\Driver\Monitoring {










function addSubscriber(Subscriber $subscriber) {}










function removeSubscriber(Subscriber $subscriber) {}








interface Subscriber {}







interface CommandSubscriber extends Subscriber
{









public function commandFailed(CommandFailedEvent $event);










public function commandStarted(CommandStartedEvent $event);










public function commandSucceeded(CommandSucceededEvent $event);
}






class CommandSucceededEvent
{
final private function __construct() {}

final public function __wakeup() {}








final public function getCommandName() {}









final public function getDurationMicros() {}










final public function getOperationId() {}









final public function getReply() {}









final public function getRequestId() {}







final public function getServer() {}
}






class CommandFailedEvent
{
final private function __construct() {}

final public function __wakeup() {}








final public function getCommandName() {}









final public function getDurationMicros() {}








final public function getError() {}










final public function getOperationId() {}









final public function getReply() {}









final public function getRequestId() {}







final public function getServer() {}
}






class CommandStartedEvent
{
final private function __construct() {}

final public function __wakeup() {}









final public function getCommand() {}








final public function getCommandName() {}








final public function getDatabaseName() {}










final public function getOperationId() {}









final public function getRequestId() {}







final public function getServer() {}
}
}





namespace MongoDB\BSON {
use DateTime;
use DateTimeInterface;
use JetBrains\PhpStorm\Deprecated;
use JsonSerializable;
use MongoDB\Driver\Exception\InvalidArgumentException;
use MongoDB\Driver\Exception\UnexpectedValueException;











function toCanonicalExtendedJSON($bson) {}










function toRelaxedExtendedJSON($bson) {}









function fromJSON($json) {}









function fromPHP($value) {}










function toJSON($bson) {}












function toPHP($bson, array $typemap = []) {}





final class Binary implements Type, BinaryInterface, \Serializable, JsonSerializable
{
public const TYPE_GENERIC = 0;
public const TYPE_FUNCTION = 1;
public const TYPE_OLD_BINARY = 2;
public const TYPE_OLD_UUID = 3;
public const TYPE_UUID = 4;
public const TYPE_MD5 = 5;




public const TYPE_ENCRYPTED = 6;
public const TYPE_USER_DEFINED = 128;







final public function __construct($data, $type) {}






final public function getData() {}






final public function getType() {}

public static function __set_state(array $properties) {}






final public function __toString() {}








final public function serialize() {}










final public function unserialize($serialized) {}








final public function jsonSerialize() {}
}





final class Decimal128 implements Type, Decimal128Interface, \Serializable, JsonSerializable
{





final public function __construct($value = '') {}






final public function __toString() {}

public static function __set_state(array $properties) {}








final public function serialize() {}










final public function unserialize($serialized) {}








final public function jsonSerialize() {}
}





final class Javascript implements Type, JavascriptInterface, \Serializable, JsonSerializable
{






final public function __construct($javascript, $scope = []) {}

public static function __set_state(array $properties) {}






final public function getCode() {}






final public function getScope() {}






final public function __toString() {}








final public function serialize() {}










final public function unserialize($serialized) {}








final public function jsonSerialize() {}
}





final class MaxKey implements Type, MaxKeyInterface, \Serializable, JsonSerializable
{
public static function __set_state(array $properties) {}








final public function serialize() {}










final public function unserialize($serialized) {}








final public function jsonSerialize() {}
}





final class MinKey implements Type, MinKeyInterface, \Serializable, JsonSerializable
{
public static function __set_state(array $properties) {}








final public function serialize() {}










final public function unserialize($serialized) {}








final public function jsonSerialize() {}
}





final class ObjectId implements Type, ObjectIdInterface, \Serializable, JsonSerializable
{






final public function __construct($id = null) {}






final public function __toString() {}

public static function __set_state(array $properties) {}







final public function getTimestamp() {}







final public function jsonSerialize() {}







final public function serialize() {}







final public function unserialize($serialized) {}
}





final class Regex implements Type, RegexInterface, \Serializable, JsonSerializable
{






final public function __construct($pattern, $flags = "") {}





final public function getFlags() {}






final public function getPattern() {}






final public function __toString() {}

public static function __set_state(array $properties) {}








final public function serialize() {}










final public function unserialize($serialized) {}








final public function jsonSerialize() {}
}





final class Timestamp implements TimestampInterface, Type, \Serializable, JsonSerializable
{






final public function __construct($increment, $timestamp) {}






final public function __toString() {}

public static function __set_state(array $properties) {}







final public function getIncrement() {}







final public function getTimestamp() {}








final public function serialize() {}










final public function unserialize($serialized) {}








final public function jsonSerialize() {}
}





final class UTCDateTime implements Type, UTCDateTimeInterface, \Serializable, \JsonSerializable
{





final public function __construct($milliseconds = null) {}

public static function __set_state(array $properties) {}






final public function toDateTime() {}






final public function __toString() {}








final public function serialize() {}










final public function unserialize($serialized) {}








final public function jsonSerialize() {}
}







#[Deprecated]
final class Undefined implements Type, \Serializable, \JsonSerializable
{
final private function __construct() {}








final public function serialize() {}










final public function unserialize($serialized) {}








final public function jsonSerialize() {}





final public function __toString() {}
}







#[Deprecated]
final class Symbol implements Type, \Serializable, \JsonSerializable
{
final private function __construct() {}








final public function serialize() {}










final public function unserialize($serialized) {}








final public function jsonSerialize() {}





final public function __toString() {}
}








#[Deprecated]
final class DBPointer implements Type, \Serializable, \JsonSerializable
{
final private function __construct() {}








final public function serialize() {}












final public function unserialize($serialized) {}








final public function jsonSerialize() {}






final public function __toString() {}
}












#[Deprecated]
final class Int64 implements Type, \Serializable, \JsonSerializable
{
final private function __construct() {}







final public function serialize() {}









final public function unserialize($serialized) {}







final public function jsonSerialize() {}





final public function __toString() {}
}





interface BinaryInterface
{




public function getData();





public function getType();






public function __toString();
}





interface ObjectIdInterface
{




public function getTimestamp();






public function __toString();
}





interface RegexInterface
{




public function getFlags();





public function getPattern();






public function __toString();
}





interface UTCDateTimeInterface
{




public function toDateTime();






public function __toString();
}





interface MaxKeyInterface {}





interface MinKeyInterface {}





interface Decimal128Interface
{





public function __toString();
}





interface Persistable extends Unserializable, Serializable {}





interface Serializable extends Type
{








public function bsonSerialize();
}





interface Unserializable extends Type
{







public function bsonUnserialize(array $data);
}





interface Type {}







interface TimestampInterface
{






public function getIncrement();







public function getTimestamp();







public function __toString();
}







interface JavascriptInterface
{






public function getCode();







public function getScope();







public function __toString();
}
}

namespace {
define('MONGODB_VERSION', '1.10.0');
define('MONGODB_STABILITY', 'stable');
}
