<?php






























































namespace Couchbase;


define("Couchbase\\HAVE_IGBINARY", 1);

define("Couchbase\\HAVE_ZLIB", 1);




define("Couchbase\\ENCODER_FORMAT_JSON", 0);



define("Couchbase\\ENCODER_FORMAT_IGBINARY", 1);



define("Couchbase\\ENCODER_FORMAT_PHP", 2);




define("Couchbase\\ENCODER_COMPRESSION_NONE", 0);



define("Couchbase\\ENCODER_COMPRESSION_ZLIB", 1);



define("Couchbase\\ENCODER_COMPRESSION_FASTLZ", 2);







function fastlzCompress($data) {}







function fastlzDecompress($data) {}








function zlibCompress($data) {}








function zlibDecompress($data) {}













function passthruDecoder($bytes, $flags, $datatype) {}












function passthruEncoder($value) {}














function defaultDecoder($bytes, $flags, $datatype) {}












function defaultEncoder($value) {}












function basicDecoderV1($bytes, $flags, $datatype, $options) {}














function basicEncoderV1($value, $options) {}




class Exception extends \Exception {}








class Document
{



public $error;




public $value;




public $flags;




public $cas;












public $token;
}







class DocumentFragment
{



public $error;




public $value;




public $cas;












public $token;
}











class Cluster
{





public function __construct($connstr) {}










public function openBucket($name = "default", $password = "") {}










public function manager($username = null, $password = null) {}











public function authenticate($authenticator) {}











public function authenticateAs($username, $password) {}
}






class ClusterManager
{



public const RBAC_DOMAIN_LOCAL = 1;




public const RBAC_DOMAIN_EXTERNAL = 2;

final private function __construct() {}






public function listBuckets() {}














public function createBucket($name, $options = []) {}









public function removeBucket($name) {}












public function info() {}











public function listUsers($domain = RBAC_DOMAIN_LOCAL) {}












public function getUser($username, $domain = RBAC_DOMAIN_LOCAL) {}













public function upsertUser($name, $settings, $domain = RBAC_DOMAIN_LOCAL) {}












public function removeUser($name, $domain = RBAC_DOMAIN_LOCAL) {}
}






class UserSettings
{










public function fullName($fullName) {}











public function password($password) {}












public function role($role, $bucket = null) {}
}














































































class Bucket
{

public const PINGSVC_KV = 0x01;


public const PINGSVC_N1QL = 0x02;


public const PINGSVC_VIEWS = 0x04;


public const PINGSVC_FTS = 0x08;

final private function __construct() {}





final private function __get($name) {}






final private function __set($name, $value) {}






public function getName() {}






public function manager() {}












public function setTranscoder($encoder, $decoder) {}




















public function get($ids, $options = []) {}






















public function getAndLock($ids, $lockTime, $options = []) {}

















public function getAndTouch($ids, $expiry, $options = []) {}


















public function getFromReplica($ids, $options = []) {}



























public function upsert($ids, $value, $options = []) {}



























public function insert($ids, $value, $options = []) {}




























public function replace($ids, $value, $options = []) {}
































public function append($ids, $value, $options = []) {}
































public function prepend($ids, $value, $options = []) {}















public function remove($ids, $options = []) {}

















public function unlock($ids, $options = []) {}

















public function touch($ids, $expiry, $options = []) {}



















public function counter($ids, $delta = 1, $options = []) {}










public function lookupIn($id) {}














public function retrieveIn($id, ...$paths) {}











public function mutateIn($id, $cas) {}














public function query($query, $jsonAsArray = false) {}












public function mapSize($id) {}













public function mapAdd($id, $key, $value) {}












public function mapRemove($id, $key) {}













public function mapGet($id, $key) {}












public function setSize($id) {}














public function setAdd($id, $value) {}













public function setExists($id, $value) {}












public function setRemove($id, $value) {}












public function listSize($id) {}












public function listPush($id, $value) {}












public function listShift($id, $value) {}












public function listRemove($id, $index) {}













public function listGet($id, $index) {}













public function listSet($id, $index, $value) {}













public function listExists($id, $value) {}












public function queueSize($id) {}













public function queueExists($id, $value) {}












public function queueAdd($id, $value) {}












public function queueRemove($id) {}
















public function ping($services = 0, $reportId = null) {}










public function diag($reportId = null) {}













public function encryptFields($document, $fieldOptions, $prefix = null) {}













public function decryptFields($document, $fieldOptions, $prefix = null) {}
}




class BucketManager
{
final private function __construct() {}













public function info() {}




public function flush() {}






public function listDesignDocuments() {}







public function getDesignDocument($name) {}






public function removeDesignDocument($name) {}







public function upsertDesignDocument($name, $document) {}







public function insertDesignDocument($name, $document) {}






public function listN1qlIndexes() {}









public function createN1qlPrimaryIndex($customName = '', $ignoreIfExist = false, $defer = false) {}











public function createN1qlIndex($name, $fields, $whereClause = '', $ignoreIfExist = false, $defer = false) {}








public function dropN1qlPrimaryIndex($customName = '', $ignoreIfNotExist = false) {}








public function dropN1qlIndex($name, $ignoreIfNotExist = false) {}
}








interface Authenticator {}












class ClassicAuthenticator implements Authenticator
{






public function cluster($username, $password) {}







public function bucket($name, $password) {}
}









class PasswordAuthenticator implements Authenticator
{






public function username($username) {}







public function password($password) {}
}




class MutationToken
{
final private function __construct() {}









public static function from($bucketName, $vbucketId, $vbucketUuid, $sequenceNumber) {}






public function bucketName() {}






public function vbucketId() {}






public function vbucketUuid() {}






public function sequenceNumber() {}
}




class MutationState
{
final private function __construct() {}









public static function from($source) {}








public function add($source) {}
}







interface ViewQueryEncodable
{





public function encode();
}













class ViewQuery implements ViewQueryEncodable
{

public const UPDATE_BEFORE = 1;


public const UPDATE_NONE = 2;


public const UPDATE_AFTER = 3;
public const ORDER_ASCENDING = 1;
public const ORDER_DESCENDING = 2;

final private function __construct() {}








public static function from($designDocumentName, $viewName) {}







public static function fromSpatial($designDocumentName, $viewName) {}






public function encode() {}







public function limit($limit) {}







public function skip($skip) {}











public function consistency($consistency) {}







public function order($order) {}







public function reduce($reduce) {}














public function group($group) {}













public function groupLevel($groupLevel) {}







public function key($key) {}







public function keys($keys) {}









public function range($startKey, $endKey, $inclusiveEnd = false) {}











public function idRange($startKeyDocumentId, $endKeyDocumentId) {}












public function custom($customParameters) {}
}













class SpatialViewQuery implements ViewQueryEncodable
{
final private function __construct() {}






public function encode() {}







public function limit($limit) {}







public function skip($skip) {}











public function consistency($consistency) {}







public function order($order) {}












public function bbox($bbox) {}










public function startRange($range) {}










public function endRange($range) {}













public function custom($customParameters) {}
}















class N1qlQuery
{






public const NOT_BOUNDED = 1;







public const REQUEST_PLUS = 2;






public const STATEMENT_PLUS = 3;




public const PROFILE_NONE = 'off';




public const PROFILE_PHASES = 'phases';




public const PROFILE_TIMINGS = 'timings';

final private function __construct() {}







public static function fromString($statement) {}











public function adhoc($adhoc) {}











public function crossBucket($crossBucket) {}












public function positionalParams($params) {}












public function namedParams($params) {}












public function consistency($consistency) {}










public function profile($profileType) {}









public function consistentWith($state) {}



















public function readonly($readonly) {}









public function scanCap($scanCap) {}







public function pipelineBatch($pipelineBatch) {}







public function pipelineCap($pipelineCap) {}







public function maxParallelism($maxParallelism) {}
}







class N1qlIndex
{
public const UNSPECIFIED = 0;
public const GSI = 1;
public const VIEW = 2;

final private function __construct() {}






public $name;






public $isPrimary;










public $type;






public $state;





public $keyspace;





public $namespace;





public $fields;











public $condition;
}











class LookupInBuilder
{
final private function __construct() {}










public function get($path, $options = []) {}












public function getCount($path, $options = []) {}












public function exists($path, $options = []) {}





public function execute() {}
}











class MutateInBuilder
{
public const FULLDOC_REPLACE = 0;
public const FULLDOC_UPSERT = 1;
public const FULLDOC_INSERT = 2;

final private function __construct() {}













public function insert($path, $value, $options = []) {}












public function modeDocument($mode) {}
















public function upsert($path, $value, $options = []) {}











public function replace($path, $value, $options = []) {}












public function remove($path, $options = []) {}













public function arrayPrepend($path, $value, $options = []) {}

















public function arrayPrependAll($path, $values, $options = []) {}













public function arrayAppend($path, $value, $options = []) {}

















public function arrayAppendAll($path, $values, $options = []) {}













public function arrayInsert($path, $value, $options = []) {}


















public function arrayInsertAll($path, $values, $options = []) {}














public function arrayAddUnique($path, $value, $options = []) {}
















public function counter($path, $delta, $options = []) {}







public function withExpiry($expiry) {}





public function execute() {}
}







class SearchQuery implements \JsonSerializable
{
public const HIGHLIGHT_HTML = 'html';
public const HIGHLIGHT_ANSI = 'ansi';
public const HIGHLIGHT_SIMPLE = 'simple';






public static function boolean() {}






public static function dateRange() {}






public static function numericRange() {}






public static function termRange() {}







public static function booleanField($value) {}







public static function conjuncts(...$queries) {}







public static function disjuncts(...$queries) {}







public static function docId(...$documentIds) {}







public static function match($match) {}






public static function matchAll() {}






public static function matchNone() {}







public static function matchPhrase(...$terms) {}







public static function prefix($prefix) {}







public static function queryString($queryString) {}







public static function regexp($regexp) {}







public static function term($term) {}







public static function wildcard($wildcard) {}









public static function geoDistance($longitude, $latitude, $distance) {}










public static function geoBoundingBox($topLeftLongitude, $topLeftLatitude, $bottomRightLongitude, $bottomRightLatitude) {}








public static function termFacet($field, $limit) {}








public static function dateRangeFacet($field, $limit) {}








public static function numericRangeFacet($field, $limit) {}









public function __construct($indexName, $queryPart) {}




public function jsonSerialize() {}







public function limit($limit) {}







public function skip($skip) {}







public function explain($explain) {}







public function serverSideTimeout($serverSideTimeout) {}










public function consistentWith($state) {}










public function fields(...$fields) {}














public function highlight($style, ...$fields) {}

















public function sort(...$sort) {}


















public function addFacet($name, $facet) {}
}






interface SearchQueryPart {}




class BooleanFieldSearchQuery implements \JsonSerializable, SearchQueryPart
{
final private function __construct() {}




public function jsonSerialize() {}





public function boost($boost) {}





public function field($field) {}
}




class BooleanSearchQuery implements \JsonSerializable, SearchQueryPart
{
final private function __construct() {}




public function jsonSerialize() {}





public function boost($boost) {}





public function must(...$queries) {}





public function mustNot(...$queries) {}





public function should(...$queries) {}
}




class ConjunctionSearchQuery implements \JsonSerializable, SearchQueryPart
{
final private function __construct() {}




public function jsonSerialize() {}





public function boost($boost) {}





public function every(...$queries) {}
}





class DisjunctionSearchQuery implements \JsonSerializable, SearchQueryPart
{
final private function __construct() {}




public function jsonSerialize() {}





public function boost($boost) {}





public function either(...$queries) {}





public function min($min) {}
}





class DateRangeSearchQuery implements \JsonSerializable, SearchQueryPart
{
final private function __construct() {}




public function jsonSerialize() {}





public function boost($boost) {}





public function field($field) {}








public function start($start, $inclusive = true) {}








public function end($end, $inclusive = false) {}





public function dateTimeParser($dateTimeParser) {}
}





class NumericRangeSearchQuery implements \JsonSerializable, SearchQueryPart
{
final private function __construct() {}




public function jsonSerialize() {}





public function boost($boost) {}





public function field($field) {}






public function min($min, $inclusive = true) {}






public function max($max, $inclusive = false) {}
}





class DocIdSearchQuery implements \JsonSerializable, SearchQueryPart
{
final private function __construct() {}




public function jsonSerialize() {}





public function boost($boost) {}





public function field($field) {}





public function docIds(...$documentIds) {}
}




class MatchAllSearchQuery implements \JsonSerializable, SearchQueryPart
{
final private function __construct() {}




public function jsonSerialize() {}





public function boost($boost) {}
}




class MatchNoneSearchQuery implements \JsonSerializable, SearchQueryPart
{
final private function __construct() {}




public function jsonSerialize() {}





public function boost($boost) {}
}





class MatchPhraseSearchQuery implements \JsonSerializable, SearchQueryPart
{
final private function __construct() {}




public function jsonSerialize() {}





public function boost($boost) {}





public function field($field) {}





public function analyzer($analyzer) {}
}





class MatchSearchQuery implements \JsonSerializable, SearchQueryPart
{
final private function __construct() {}




public function jsonSerialize() {}





public function boost($boost) {}





public function field($field) {}





public function analyzer($analyzer) {}





public function prefixLength($prefixLength) {}





public function fuzziness($fuzziness) {}
}






class PhraseSearchQuery implements \JsonSerializable, SearchQueryPart
{
final private function __construct() {}




public function jsonSerialize() {}





public function boost($boost) {}





public function field($field) {}
}




class RegexpSearchQuery implements \JsonSerializable, SearchQueryPart
{
final private function __construct() {}




public function jsonSerialize() {}





public function boost($boost) {}





public function field($field) {}
}




class WildcardSearchQuery implements \JsonSerializable, SearchQueryPart
{
final private function __construct() {}




public function jsonSerialize() {}





public function boost($boost) {}





public function field($field) {}
}




class PrefixSearchQuery implements \JsonSerializable, SearchQueryPart
{
final private function __construct() {}




public function jsonSerialize() {}





public function boost($boost) {}





public function field($field) {}
}




class QueryStringSearchQuery implements \JsonSerializable, SearchQueryPart
{
final private function __construct() {}




public function jsonSerialize() {}





public function boost($boost) {}
}




class TermSearchQuery implements \JsonSerializable, SearchQueryPart
{
final private function __construct() {}




public function jsonSerialize() {}





public function boost($boost) {}





public function field($field) {}





public function prefixLength($prefixLength) {}





public function fuzziness($fuzziness) {}
}





class TermRangeSearchQuery implements \JsonSerializable, SearchQueryPart
{
final private function __construct() {}




public function jsonSerialize() {}





public function boost($boost) {}





public function field($field) {}






public function min($min, $inclusive = true) {}






public function max($max, $inclusive = false) {}
}






class GeoDistanceSearchQuery implements \JsonSerializable, SearchQueryPart
{
final private function __construct() {}




public function jsonSerialize() {}





public function boost($boost) {}





public function field($field) {}
}




class GeoBoundingBoxSearchQuery implements \JsonSerializable, SearchQueryPart
{
final private function __construct() {}




public function jsonSerialize() {}





public function boost($boost) {}





public function field($field) {}
}









interface SearchFacet {}




class TermSearchFacet implements \JsonSerializable, SearchFacet
{
final private function __construct() {}




public function jsonSerialize() {}
}




class DateRangeSearchFacet implements \JsonSerializable, SearchFacet
{
final private function __construct() {}




public function jsonSerialize() {}







public function addRange($name, $start, $end) {}
}




class NumericRangeSearchFacet implements \JsonSerializable, SearchFacet
{
final private function __construct() {}




public function jsonSerialize() {}







public function addRange($name, $min, $max) {}
}




class SearchSort
{
private function __construct() {}






public static function id() {}






public static function score() {}








public static function field($field) {}










public static function geoDistance($field, $longitude, $latitude) {}
}




class SearchSortId extends SearchSort implements \JsonSerializable
{
private function __construct() {}








public function descending($descending) {}
}




class SearchSortScore extends SearchSort implements \JsonSerializable
{
private function __construct() {}








public function descending($descending) {}
}




class SearchSortField extends SearchSort implements \JsonSerializable
{
public const TYPE_AUTO = "auto";
public const TYPE_STRING = "string";
public const TYPE_NUMBER = "number";
public const TYPE_DATE = "date";
public const MODE_DEFAULT = "default";
public const MODE_MIN = "min";
public const MODE_MAX = "max";
public const MISSING_FIRST = "first";
public const MISSING_LAST = "last";

private function __construct() {}








public function descending($descending) {}











public function type($type) {}









public function mode($mode) {}









public function missing($missing) {}
}




class SearchSortGeoDistance extends SearchSort implements \JsonSerializable
{
private function __construct() {}








public function descending($descending) {}








public function unit($unit) {}
}







class AnalyticsQuery
{
final private function __construct() {}







public static function fromString($statement) {}
}
