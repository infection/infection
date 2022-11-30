<?php
































































namespace Couchbase;

use JsonSerializable;
use Exception;
use Throwable;
use DateTimeInterface;




interface MutationToken
{





public function bucketName();






public function partitionId();






public function partitionUuid();






public function sequenceNumber();
}




interface QueryMetaData
{





public function status(): ?string;






public function requestId(): ?string;






public function clientContextId(): ?string;






public function signature(): ?array;






public function warnings(): ?array;






public function errors(): ?array;






public function metrics(): ?array;






public function profile(): ?array;
}




interface SearchMetaData
{





public function successCount(): ?int;






public function errorCount(): ?int;






public function took(): ?int;






public function totalHits(): ?int;






public function maxScore(): ?float;






public function metrics(): ?array;
}




interface ViewMetaData
{





public function totalRows(): ?int;






public function debug(): ?array;
}




interface Result
{





public function cas(): ?string;
}




interface GetResult extends Result
{





public function content(): ?array;








public function expiryTime(): ?DateTimeInterface;
}




interface GetReplicaResult extends Result
{





public function content(): ?array;






public function isReplica(): bool;
}




interface ExistsResult extends Result
{





public function exists(): bool;
}




interface MutationResult extends Result
{





public function mutationToken(): ?MutationToken;
}




interface CounterResult extends MutationResult
{





public function content(): int;
}




interface LookupInResult extends Result
{






public function content(int $index): ?object;







public function exists(int $index): bool;







public function status(int $index): int;








public function expiryTime(): ?DateTimeInterface;
}




interface MutateInResult extends MutationResult
{






public function content(int $index): ?array;
}




interface QueryResult
{





public function metaData(): ?QueryMetaData;






public function rows(): ?array;
}




interface AnalyticsResult
{





public function metaData(): ?QueryMetaData;






public function rows(): ?array;
}





interface TermFacetResult
{



public function term(): string;




public function count(): int;
}





interface NumericRangeFacetResult
{



public function name(): string;




public function min();




public function max();




public function count(): int;
}





interface DateRangeFacetResult
{



public function name(): string;




public function start(): ?string;




public function end(): ?string;




public function count(): int;
}






interface SearchFacetResult
{





public function field(): string;






public function total(): int;







public function missing(): int;








public function other(): int;




public function terms(): ?array;




public function numericRanges(): ?array;




public function dateRanges(): ?array;
}




interface SearchResult
{





public function metaData(): ?SearchMetaData;







public function facets(): ?array;






public function rows(): ?array;
}




interface ViewResult
{





public function metaData(): ?ViewMetaData;






public function rows(): ?array;
}




class ViewRow
{





public function id(): ?string {}




public function key() {}




public function value() {}




public function document() {}
}




class BaseException extends Exception implements Throwable
{





public function ref(): ?string {}






public function context(): ?object {}
}

class RequestCanceledException extends BaseException implements Throwable {}




class HttpException extends BaseException implements Throwable {}

class ParsingFailureException extends HttpException implements Throwable {}

class IndexNotFoundException extends HttpException implements Throwable {}

class PlanningFailureException extends HttpException implements Throwable {}

class IndexFailureException extends HttpException implements Throwable {}

class KeyspaceNotFoundException extends HttpException implements Throwable {}




class QueryException extends HttpException implements Throwable {}




class QueryErrorException extends QueryException implements Throwable {}

class DmlFailureException extends QueryException implements Throwable {}

class PreparedStatementException extends QueryException implements Throwable {}

class QueryServiceException extends QueryException implements Throwable {}




class SearchException extends HttpException implements Throwable {}




class AnalyticsException extends HttpException implements Throwable {}




class ViewException extends HttpException implements Throwable {}

class PartialViewException extends HttpException implements Throwable {}

class BindingsException extends BaseException implements Throwable {}

class InvalidStateException extends BaseException implements Throwable {}




class KeyValueException extends BaseException implements Throwable {}




class DocumentNotFoundException extends KeyValueException implements Throwable {}




class KeyExistsException extends KeyValueException implements Throwable {}




class ValueTooBigException extends KeyValueException implements Throwable {}




class KeyLockedException extends KeyValueException implements Throwable {}




class TempFailException extends KeyValueException implements Throwable {}




class PathNotFoundException extends KeyValueException implements Throwable {}




class PathExistsException extends KeyValueException implements Throwable {}




class InvalidRangeException extends KeyValueException implements Throwable {}




class KeyDeletedException extends KeyValueException implements Throwable {}




class CasMismatchException extends KeyValueException implements Throwable {}




class InvalidConfigurationException extends BaseException implements Throwable {}




class ServiceMissingException extends BaseException implements Throwable {}




class NetworkException extends BaseException implements Throwable {}




class TimeoutException extends BaseException implements Throwable {}




class BucketMissingException extends BaseException implements Throwable {}




class ScopeMissingException extends BaseException implements Throwable {}




class CollectionMissingException extends BaseException implements Throwable {}




class AuthenticationException extends BaseException implements Throwable {}




class BadInputException extends BaseException implements Throwable {}




class DurabilityException extends BaseException implements Throwable {}




class SubdocumentException extends BaseException implements Throwable {}

class QueryIndex
{
public function name(): string {}

public function isPrimary(): bool {}

public function type(): string {}

public function state(): string {}

public function keyspace(): string {}

public function indexKey(): array {}

public function condition(): ?string {}
}

class CreateQueryIndexOptions
{
public function condition(string $condition): CreateQueryIndexOptions {}

public function ignoreIfExists(bool $shouldIgnore): CreateQueryIndexOptions {}

public function numReplicas(int $number): CreateQueryIndexOptions {}

public function deferred(bool $isDeferred): CreateQueryIndexOptions {}
}

class CreateQueryPrimaryIndexOptions
{
public function indexName(string $name): CreateQueryPrimaryIndexOptions {}

public function ignoreIfExists(bool $shouldIgnore): CreateQueryPrimaryIndexOptions {}

public function numReplicas(int $number): CreateQueryPrimaryIndexOptions {}

public function deferred(bool $isDeferred): CreateQueryPrimaryIndexOptions {}
}

class DropQueryIndexOptions
{
public function ignoreIfNotExists(bool $shouldIgnore): DropQueryIndexOptions {}
}

class DropQueryPrimaryIndexOptions
{
public function indexName(string $name): DropQueryPrimaryIndexOptions {}

public function ignoreIfNotExists(bool $shouldIgnore): DropQueryPrimaryIndexOptions {}
}

class WatchQueryIndexesOptions
{
public function watchPrimary(bool $shouldWatch): WatchQueryIndexesOptions {}
}

class QueryIndexManager
{
public function getAllIndexes(string $bucketName): array {}

public function createIndex(string $bucketName, string $indexName, array $fields, CreateQueryIndexOptions $options = null) {}

public function createPrimaryIndex(string $bucketName, CreateQueryPrimaryIndexOptions $options = null) {}

public function dropIndex(string $bucketName, string $indexName, DropQueryIndexOptions $options = null) {}

public function dropPrimaryIndex(string $bucketName, DropQueryPrimaryIndexOptions $options = null) {}

public function watchIndexes(string $bucketName, array $indexNames, int $timeout, WatchQueryIndexesOptions $options = null) {}

public function buildDeferredIndexes(string $bucketName) {}
}

class SearchIndex implements JsonSerializable
{
public function jsonSerialize() {}

public function type(): string {}

public function uuid(): string {}

public function params(): array {}

public function sourceType(): string {}

public function sourceUuid(): string {}

public function sourceName(): string {}

public function sourceParams(): array {}

public function setType(string $type): SearchIndex {}

public function setUuid(string $uuid): SearchIndex {}

public function setParams(string $params): SearchIndex {}

public function setSourceType(string $type): SearchIndex {}

public function setSourceUuid(string $uuid): SearchIndex {}

public function setSourcename(string $params): SearchIndex {}

public function setSourceParams(string $params): SearchIndex {}
}

class SearchIndexManager
{
public function getIndex(string $name): SearchIndex {}

public function getAllIndexes(): array {}

public function upsertIndex(SearchIndex $indexDefinition) {}

public function dropIndex(string $name) {}

public function getIndexedDocumentsCount(string $indexName): int {}

public function pauseIngest(string $indexName) {}

public function resumeIngest(string $indexName) {}

public function allowQuerying(string $indexName) {}

public function disallowQuerying(string $indexName) {}

public function freezePlan(string $indexName) {}

public function unfreezePlan(string $indexName) {}

public function analyzeDocument(string $indexName, $document) {}
}





class Cluster
{
public function __construct(string $connstr, ClusterOptions $options) {}







public function bucket(string $name): Bucket {}









public function query(string $statement, QueryOptions $options = null): QueryResult {}









public function analyticsQuery(string $statement, AnalyticsOptions $options = null): AnalyticsResult {}










public function searchQuery(string $indexName, SearchQuery $query, SearchOptions $options = null): SearchResult {}






public function buckets(): BucketManager {}






public function users(): UserManager {}






public function queryIndexes(): QueryIndexManager {}






public function searchIndexes(): SearchIndexManager {}
}

interface EvictionPolicy
{







public const FULL = "fullEviction";








public const VALUE_ONLY = "valueOnly";







public const NO_EVICTION = "noEviction";






public const NOT_RECENTLY_USED = "nruEviction";
}

class BucketSettings
{
public function name(): string {}

public function flushEnabled(): bool {}

public function ramQuotaMb(): int {}

public function numReplicas(): int {}

public function replicaIndexes(): bool {}

public function bucketType(): string {}

public function evictionPolicy(): string {}

public function maxTtl(): int {}

public function compressionMode(): string {}

public function setName(string $name): BucketSettings {}

public function enableFlush(bool $enable): BucketSettings {}

public function setRamQuotaMb(int $sizeInMb): BucketSettings {}

public function setNumReplicas(int $numReplicas): BucketSettings {}

public function enableReplicaIndexes(bool $enable): BucketSettings {}

public function setBucketType(string $type): BucketSettings {}












public function setEvictionPolicy(string $policy): BucketSettings {}

public function setMaxTtl(int $ttlSeconds): BucketSettings {}

public function setCompressionMode(string $mode): BucketSettings {}









public function minimalDurabilityLevel(): int {}











public function setMinimalDurabilityLevel(int $durabilityLevel): BucketSettings {}
}

class BucketManager
{
public function createBucket(BucketSettings $settings) {}

public function removeBucket(string $name) {}

public function getBucket(string $name): BucketSettings {}

public function getAllBuckets(): array {}

public function flush(string $name) {}
}

class Role
{
public function name(): string {}

public function bucket(): ?string {}

public function scope(): ?string {}

public function collection(): ?string {}

public function setName(string $name): Role {}

public function setBucket(string $bucket): Role {}

public function setScope(string $bucket): Role {}

public function setCollection(string $bucket): Role {}
}

class RoleAndDescription
{
public function role(): Role {}

public function displayName(): string {}

public function description(): string {}
}

class Origin
{
public function type(): string {}

public function name(): string {}
}

class RoleAndOrigin
{
public function role(): Role {}

public function origins(): array {}
}

class User
{
public function username(): string {}

public function displayName(): string {}

public function groups(): array {}

public function roles(): array {}

public function setUsername(string $username): User {}

public function setPassword(string $password): User {}

public function setDisplayName(string $name): User {}

public function setGroups(array $groups): User {}

public function setRoles(array $roles): User {}
}

class Group
{
public function name(): string {}

public function description(): string {}

public function roles(): array {}

public function ldapGroupReference(): ?string {}

public function setName(string $name): Group {}

public function setDescription(string $description): Group {}

public function setRoles(array $roles): Group {}
}

class UserAndMetadata
{
public function domain(): string {}

public function user(): User {}

public function effectiveRoles(): array {}

public function passwordChanged(): string {}

public function externalGroups(): array {}
}

class GetAllUsersOptions
{
public function domainName(string $name): GetAllUsersOptions {}
}

class GetUserOptions
{
public function domainName(string $name): GetUserOptions {}
}

class DropUserOptions
{
public function domainName(string $name): DropUserOptions {}
}

class UpsertUserOptions
{
public function domainName(string $name): DropUserOptions {}
}

class UserManager
{
public function getUser(string $name, GetUserOptions $options = null): UserAndMetadata {}

public function getAllUsers(GetAllUsersOptions $options = null): array {}

public function upsertUser(User $user, UpsertUserOptions $options = null) {}

public function dropUser(string $name, DropUserOptions $options = null) {}

public function getRoles(): array {}

public function getGroup(string $name): Group {}

public function getAllGroups(): array {}

public function upsertGroup(Group $group) {}

public function dropGroup(string $name) {}
}




class BinaryCollection
{





public function name(): string {}









public function append(string $id, string $value, AppendOptions $options = null): MutationResult {}









public function prepend(string $id, string $value, PrependOptions $options = null): MutationResult {}








public function increment(string $id, IncrementOptions $options = null): CounterResult {}








public function decrement(string $id, DecrementOptions $options = null): CounterResult {}
}




class Collection
{





public function name(): string {}












public function get(string $id, GetOptions $options = null): GetResult {}








public function exists(string $id, ExistsOptions $options = null): ExistsResult {}










public function getAndLock(string $id, int $lockTime, GetAndLockOptions $options = null): GetResult {}









public function getAndTouch(string $id, int $expiry, GetAndTouchOptions $options = null): GetResult {}








public function getAnyReplica(string $id, GetAnyReplicaOptions $options = null): GetReplicaResult {}









public function getAllReplicas(string $id, GetAllReplicasOptions $options = null): array {}









public function upsert(string $id, $value, UpsertOptions $options = null): MutationResult {}









public function insert(string $id, $value, InsertOptions $options = null): MutationResult {}









public function replace(string $id, $value, ReplaceOptions $options = null): MutationResult {}








public function remove(string $id, RemoveOptions $options = null): MutationResult {}










public function unlock(string $id, string $cas, UnlockOptions $options = null): Result {}









public function touch(string $id, int $expiry, TouchOptions $options = null): MutationResult {}









public function lookupIn(string $id, array $specs, LookupInOptions $options = null): LookupInResult {}









public function mutateIn(string $id, array $specs, MutateInOptions $options = null): MutateInResult {}






public function binary(): BinaryCollection {}
}




class Scope
{
public function __construct(Bucket $bucket, string $name) {}






public function name(): string {}







public function collection(string $name): Collection {}








public function query(string $statement, QueryOptions $options = null): QueryResult {}
}

class ScopeSpec
{
public function name(): string {}

public function collections(): array {}
}

class CollectionSpec
{
public function name(): string {}

public function scopeName(): string {}

public function setName(string $name): CollectionSpec {}

public function setScopeName(string $name): CollectionSpec {}

public function setMaxExpiry(int $ms): CollectionSpec {}
}

class CollectionManager
{
public function getScope(string $name): ScopeSpec {}

public function getAllScopes(): array {}

public function createScope(string $name) {}

public function dropScope(string $name) {}

public function createCollection(CollectionSpec $collection) {}

public function dropCollection(CollectionSpec $collection) {}
}





class Bucket
{





public function defaultScope(): Scope {}






public function defaultCollection(): Collection {}







public function scope(string $name): Scope {}







public function setTranscoder(callable $encoder, callable $decoder) {}






public function name(): string {}









public function viewQuery(string $designDoc, string $viewName, ViewOptions $options = null): ViewResult {}






public function collections(): CollectionManager {}






public function viewIndexes(): ViewIndexManager {}








public function ping($services, $reportId) {}







public function diagnostics($reportId): array {}
}

class View
{
public function name(): string {}

public function map(): string {}

public function reduce(): string {}

public function setName(string $name): View {}

public function setMap(string $mapJsCode): View {}

public function setReduce(string $reduceJsCode): View {}
}

class DesignDocument
{
public function name(): string {}

public function views(): array {}

public function setName(string $name): DesignDocument {}

public function setViews(array $views): DesignDocument {}
}

class ViewIndexManager
{
public function getAllDesignDocuments(): array {}

public function getDesignDocument(string $name): DesignDocument {}

public function dropDesignDocument(string $name) {}

public function upsertDesignDocument(DesignDocument $document) {}
}




class MutationState
{
public function __construct() {}







public function add(MutationResult $source): MutationState {}
}

class AnalyticsOptions
{
public function timeout(int $arg): AnalyticsOptions {}

public function namedParameters(array $pairs): AnalyticsOptions {}

public function positionalParameters(array $args): AnalyticsOptions {}

public function raw(string $key, $value): AnalyticsOptions {}

public function clientContextId(string $value): AnalyticsOptions {}

public function priority(bool $urgent): AnalyticsOptions {}

public function readonly(bool $arg): AnalyticsOptions {}

public function scanConsistency(string $arg): AnalyticsOptions {}
}




interface LookupInSpec {}




class LookupGetSpec implements LookupInSpec
{
public function __construct(string $path, bool $isXattr = false) {}
}




class LookupCountSpec implements LookupInSpec
{
public function __construct(string $path, bool $isXattr = false) {}
}




class LookupExistsSpec implements LookupInSpec
{
public function __construct(string $path, bool $isXattr = false) {}
}




class LookupGetFullSpec implements LookupInSpec
{
public function __construct() {}
}




interface MutateInSpec {}




class MutateInsertSpec implements MutateInSpec
{
public function __construct(string $path, $value, bool $isXattr, bool $createPath, bool $expandMacros) {}
}




class MutateUpsertSpec implements MutateInSpec
{
public function __construct(string $path, $value, bool $isXattr, bool $createPath, bool $expandMacros) {}
}




class MutateReplaceSpec implements MutateInSpec
{
public function __construct(string $path, $value, bool $isXattr) {}
}




class MutateRemoveSpec implements MutateInSpec
{
public function __construct(string $path, bool $isXattr) {}
}




class MutateArrayAppendSpec implements MutateInSpec
{
public function __construct(string $path, array $values, bool $isXattr, bool $createPath, bool $expandMacros) {}
}




class MutateArrayPrependSpec implements MutateInSpec
{
public function __construct(string $path, array $values, bool $isXattr, bool $createPath, bool $expandMacros) {}
}




class MutateArrayInsertSpec implements MutateInSpec
{
public function __construct(string $path, array $values, bool $isXattr, bool $createPath, bool $expandMacros) {}
}





class MutateArrayAddUniqueSpec implements MutateInSpec
{
public function __construct(string $path, $value, bool $isXattr, bool $createPath, bool $expandMacros) {}
}




class MutateCounterSpec implements MutateInSpec
{
public function __construct(string $path, int $delta, bool $isXattr, bool $createPath) {}
}

class SearchOptions implements JsonSerializable
{
public function jsonSerialize() {}







public function timeout(int $ms): SearchOptions {}







public function limit(int $limit): SearchOptions {}







public function skip(int $skip): SearchOptions {}







public function explain(bool $explain): SearchOptions {}







public function disableScoring(bool $disabled): SearchOptions {}










public function consistentWith(string $index, MutationState $state): SearchOptions {}










public function fields(array $fields): SearchOptions {}

















public function facets(array $facets): SearchOptions {}

















public function sort(array $specs): SearchOptions {}














public function highlight(string $style = null, array $fields = null): SearchOptions {}
}

interface SearchHighlightMode
{
public const HTML = "html";
public const ANSI = "ansi";
public const SIMPLE = "simple";
}









interface SearchQuery {}




class BooleanFieldSearchQuery implements JsonSerializable, SearchQuery
{
public function jsonSerialize() {}

public function __construct(bool $arg) {}





public function boost(float $boost): BooleanFieldSearchQuery {}





public function field(string $field): BooleanFieldSearchQuery {}
}




class BooleanSearchQuery implements JsonSerializable, SearchQuery
{
public function jsonSerialize() {}

public function __construct() {}





public function boost($boost): BooleanSearchQuery {}





public function must(ConjunctionSearchQuery $query): BooleanSearchQuery {}





public function mustNot(DisjunctionSearchQuery $query): BooleanSearchQuery {}





public function should(DisjunctionSearchQuery $query): BooleanSearchQuery {}
}




class ConjunctionSearchQuery implements JsonSerializable, SearchQuery
{
public function jsonSerialize() {}

public function __construct(array $queries) {}





public function boost($boost): ConjunctionSearchQuery {}





public function every(SearchQuery ...$queries): ConjunctionSearchQuery {}
}





class DateRangeSearchQuery implements JsonSerializable, SearchQuery
{
public function jsonSerialize() {}

public function __construct() {}





public function boost(float $boost): DateRangeSearchQuery {}





public function field(string $field): DateRangeSearchQuery {}








public function start($start, bool $inclusive = false): DateRangeSearchQuery {}








public function end($end, bool $inclusive = false): DateRangeSearchQuery {}





public function dateTimeParser(string $dateTimeParser): DateRangeSearchQuery {}
}





class DisjunctionSearchQuery implements JsonSerializable, SearchQuery
{
public function jsonSerialize() {}

public function __construct(array $queries) {}





public function boost(float $boost): DisjunctionSearchQuery {}





public function either(SearchQuery ...$queries): DisjunctionSearchQuery {}





public function min(int $min): DisjunctionSearchQuery {}
}





class DocIdSearchQuery implements JsonSerializable, SearchQuery
{
public function jsonSerialize() {}

public function __construct() {}





public function boost(float $boost): DocIdSearchQuery {}





public function field(string $field): DocIdSearchQuery {}





public function docIds(string ...$documentIds): DocIdSearchQuery {}
}




class GeoBoundingBoxSearchQuery implements JsonSerializable, SearchQuery
{
public function jsonSerialize() {}

public function __construct(float $top_left_longitude, float $top_left_latitude, float $buttom_right_longitude, float $buttom_right_latitude) {}





public function boost(float $boost): GeoBoundingBoxSearchQuery {}





public function field(string $field): GeoBoundingBoxSearchQuery {}
}






class GeoDistanceSearchQuery implements JsonSerializable, SearchQuery
{
public function jsonSerialize() {}

public function __construct(float $longitude, float $latitude, string $distance = null) {}





public function boost(float $boost): GeoDistanceSearchQuery {}





public function field(string $field): GeoDistanceSearchQuery {}
}

class Coordinate implements JsonSerializable
{
public function jsonSerialize() {}







public function __construct(float $longitude, float $latitude) {}
}




class GeoPolygonQuery implements JsonSerializable, SearchQuery
{
public function jsonSerialize() {}






public function __construct(array $coordinates) {}





public function boost(float $boost): GeoPolygonQuery {}





public function field(string $field): GeoPolygonQuery {}
}




class MatchAllSearchQuery implements JsonSerializable, SearchQuery
{
public function jsonSerialize() {}

public function __construct() {}





public function boost(float $boost): MatchAllSearchQuery {}
}




class MatchNoneSearchQuery implements JsonSerializable, SearchQuery
{
public function jsonSerialize() {}

public function __construct() {}





public function boost(float $boost): MatchNoneSearchQuery {}
}





class MatchPhraseSearchQuery implements JsonSerializable, SearchQuery
{
public function jsonSerialize() {}

public function __construct(string $value) {}





public function boost(float $boost): MatchPhraseSearchQuery {}





public function field(string $field): MatchPhraseSearchQuery {}





public function analyzer(string $analyzer): MatchPhraseSearchQuery {}
}





class MatchSearchQuery implements JsonSerializable, SearchQuery
{
public function jsonSerialize() {}

public function __construct(string $value) {}





public function boost(float $boost): MatchSearchQuery {}





public function field(string $field): MatchSearchQuery {}





public function analyzer(string $analyzer): MatchSearchQuery {}





public function prefixLength(int $prefixLength): MatchSearchQuery {}





public function fuzziness(int $fuzziness): MatchSearchQuery {}
}





class NumericRangeSearchQuery implements JsonSerializable, SearchQuery
{
public function jsonSerialize() {}

public function __construct() {}





public function boost(float $boost): NumericRangeSearchQuery {}





public function field($field): NumericRangeSearchQuery {}






public function min(float $min, bool $inclusive = false): NumericRangeSearchQuery {}






public function max(float $max, bool $inclusive = false): NumericRangeSearchQuery {}
}






class PhraseSearchQuery implements JsonSerializable, SearchQuery
{
public function jsonSerialize() {}

public function __construct(string ...$terms) {}





public function boost(float $boost): PhraseSearchQuery {}





public function field(string $field): PhraseSearchQuery {}
}




class PrefixSearchQuery implements JsonSerializable, SearchQuery
{
public function jsonSerialize() {}

public function __construct(string $prefix) {}





public function boost(float $boost): PrefixSearchQuery {}





public function field(string $field): PrefixSearchQuery {}
}




class QueryStringSearchQuery implements JsonSerializable, SearchQuery
{
public function jsonSerialize() {}

public function __construct(string $query_string) {}





public function boost(float $boost): QueryStringSearchQuery {}
}




class RegexpSearchQuery implements JsonSerializable, SearchQuery
{
public function jsonSerialize() {}

public function __construct(string $regexp) {}





public function boost(float $boost): RegexpSearchQuery {}





public function field(string $field): RegexpSearchQuery {}
}




class TermSearchQuery implements JsonSerializable, SearchQuery
{
public function jsonSerialize() {}

public function __construct(string $term) {}





public function boost(float $boost): TermSearchQuery {}





public function field(string $field): TermSearchQuery {}





public function prefixLength(int $prefixLength): TermSearchQuery {}





public function fuzziness(int $fuzziness): TermSearchQuery {}
}





class TermRangeSearchQuery implements JsonSerializable, SearchQuery
{
public function jsonSerialize() {}

public function __construct() {}





public function boost(float $boost): TermRangeSearchQuery {}





public function field(string $field): TermRangeSearchQuery {}






public function min(string $min, bool $inclusive = true): TermRangeSearchQuery {}






public function max(string $max, bool $inclusive = false): TermRangeSearchQuery {}
}




class WildcardSearchQuery implements JsonSerializable, SearchQuery
{
public function jsonSerialize() {}

public function __construct(string $wildcard) {}





public function boost(float $boost): WildcardSearchQuery {}





public function field(string $field): WildcardSearchQuery {}
}









interface SearchFacet {}




class TermSearchFacet implements JsonSerializable, SearchFacet
{
public function jsonSerialize() {}

public function __construct(string $field, int $limit) {}
}




class NumericRangeSearchFacet implements JsonSerializable, SearchFacet
{
public function jsonSerialize() {}

public function __construct(string $field, int $limit) {}







public function addRange(string $name, float $min = null, float $max = null): NumericRangeSearchFacet {}
}




class DateRangeSearchFacet implements JsonSerializable, SearchFacet
{
public function jsonSerialize() {}

public function __construct(string $field, int $limit) {}







public function addRange(string $name, $start = null, $end = null): DateRangeSearchFacet {}
}




interface SearchSort {}




class SearchSortField implements JsonSerializable, SearchSort
{
public function jsonSerialize() {}

public function __construct(string $field) {}








public function descending(bool $descending): SearchSortField {}











public function type(string $type): SearchSortField {}









public function mode(string $mode): SearchSortField {}









public function missing(string $missing): SearchSortField {}
}

interface SearchSortType
{
public const AUTO = "auto";
public const STRING = "string";
public const NUMBER = "number";
public const DATE = "date";
}

interface SearchSortMode
{
public const DEFAULT = "default";
public const MIN = "min";
public const MAX = "max";
}

interface SearchSortMissing
{
public const FIRST = "first";
public const LAST = "last";
}




class SearchSortGeoDistance implements JsonSerializable, SearchSort
{
public function jsonSerialize() {}

public function __construct(string $field, float $logitude, float $latitude) {}








public function descending(bool $descending): SearchSortGeoDistance {}








public function unit(string $unit): SearchSortGeoDistance {}
}




class SearchSortId implements JsonSerializable, SearchSort
{
public function jsonSerialize() {}

public function __construct() {}








public function descending(bool $descending): SearchSortId {}
}




class SearchSortScore implements JsonSerializable, SearchSort
{
public function jsonSerialize() {}

public function __construct() {}








public function descending(bool $descending): SearchSortScore {}
}

class GetOptions
{






public function timeout(int $arg): GetOptions {}











public function withExpiry(bool $arg): GetOptions {}












public function project(array $arg): GetOptions {}








public function decoder(callable $arg): GetOptions {}
}

class GetAndTouchOptions
{






public function timeout(int $arg): GetAndTouchOptions {}








public function decoder(callable $arg): GetAndTouchOptions {}
}

class GetAndLockOptions
{






public function timeout(int $arg): GetAndLockOptions {}








public function decoder(callable $arg): GetAndLockOptions {}
}

class GetAllReplicasOptions
{






public function timeout(int $arg): GetAllReplicasOptions {}








public function decoder(callable $arg): GetAllReplicasOptions {}
}

class GetAnyReplicaOptions
{






public function timeout(int $arg): GetAnyReplicaOptions {}








public function decoder(callable $arg): GetAnyReplicaOptions {}
}

class ExistsOptions
{






public function timeout(int $arg): ExistsOptions {}
}

class UnlockOptions
{






public function timeout(int $arg): UnlockOptions {}
}

class InsertOptions
{






public function timeout(int $arg): InsertOptions {}







public function expiry(int $arg): InsertOptions {}







public function durabilityLevel(int $arg): InsertOptions {}








public function encoder(callable $arg): InsertOptions {}
}

class UpsertOptions
{






public function timeout(int $arg): UpsertOptions {}







public function expiry(mixed $arg): UpsertOptions {}







public function durabilityLevel(int $arg): UpsertOptions {}








public function encoder(callable $arg): UpsertOptions {}
}

class ReplaceOptions
{






public function timeout(int $arg): ReplaceOptions {}







public function expiry(mixed $arg): ReplaceOptions {}







public function cas(string $arg): ReplaceOptions {}







public function durabilityLevel(int $arg): ReplaceOptions {}








public function encoder(callable $arg): ReplaceOptions {}
}

class AppendOptions
{






public function timeout(int $arg): AppendOptions {}







public function durabilityLevel(int $arg): AppendOptions {}
}

class PrependOptions
{






public function timeout(int $arg): PrependOptions {}







public function durabilityLevel(int $arg): PrependOptions {}
}





interface DurabilityLevel
{



public const NONE = 0;





public const MAJORITY = 1;






public const MAJORITY_AND_PERSIST_TO_ACTIVE = 2;





public const PERSIST_TO_MAJORITY = 3;
}

class TouchOptions
{






public function timeout(int $arg): TouchOptions {}
}

class IncrementOptions
{






public function timeout(int $arg): IncrementOptions {}







public function expiry(mixed $arg): IncrementOptions {}







public function durabilityLevel(int $arg): IncrementOptions {}







public function delta(int $arg): IncrementOptions {}








public function initial(int $arg): IncrementOptions {}
}

class DecrementOptions
{






public function timeout(int $arg): DecrementOptions {}







public function expiry(mixed $arg): DecrementOptions {}







public function durabilityLevel(int $arg): DecrementOptions {}







public function delta(int $arg): DecrementOptions {}








public function initial(int $arg): DecrementOptions {}
}

class RemoveOptions
{






public function timeout(int $arg): RemoveOptions {}







public function durabilityLevel(int $arg): RemoveOptions {}







public function cas(string $arg): RemoveOptions {}
}

class LookupInOptions
{






public function timeout(int $arg): LookupInOptions {}












public function withExpiry(bool $arg): LookupInOptions {}
}

class MutateInOptions
{






public function timeout(int $arg): MutateInOptions {}







public function cas(string $arg): MutateInOptions {}







public function expiry(mixed $arg): MutateInOptions {}







public function durabilityLevel(int $arg): MutateInOptions {}







public function storeSemantics(int $arg): MutateInOptions {}
}





interface StoreSemantics
{



public const REPLACE = 0;




public const UPSERT = 1;




public const INSERT = 2;
}

class ViewOptions
{
public function timeout(int $arg): ViewOptions {}

public function includeDocuments(bool $arg, int $maxConcurrentDocuments = 10): ViewOptions {}

public function key($arg): ViewOptions {}

public function keys(array $args): ViewOptions {}

public function limit(int $arg): ViewOptions {}

public function skip(int $arg): ViewOptions {}

public function scanConsistency(int $arg): ViewOptions {}

public function order(int $arg): ViewOptions {}

public function reduce(bool $arg): ViewOptions {}

public function group(bool $arg): ViewOptions {}

public function groupLevel(int $arg): ViewOptions {}

public function range($start, $end, $inclusiveEnd = false): ViewOptions {}

public function idRange($start, $end, $inclusiveEnd = false): ViewOptions {}

public function raw(string $key, $value): ViewOptions {}
}

interface ViewConsistency
{
public const NOT_BOUNDED = 0;
public const REQUEST_PLUS = 1;
public const UPDATE_AFTER = 2;
}

interface ViewOrdering
{
public const ASCENDING = 0;
public const DESCENDING = 1;
}

class QueryOptions
{






public function timeout(int $arg): QueryOptions {}







public function consistentWith(MutationState $arg): QueryOptions {}







public function scanConsistency(int $arg): QueryOptions {}







public function scanCap(int $arg): QueryOptions {}







public function pipelineCap(int $arg): QueryOptions {}







public function pipelineBatch(int $arg): QueryOptions {}







public function maxParallelism(int $arg): QueryOptions {}







public function profile(int $arg): QueryOptions {}







public function readonly(bool $arg): QueryOptions {}







public function flexIndex(bool $arg): QueryOptions {}







public function adhoc(bool $arg): QueryOptions {}







public function namedParameters(array $pairs): QueryOptions {}







public function positionalParameters(array $args): QueryOptions {}








public function raw(string $key, $value): QueryOptions {}







public function clientContextId(string $arg): QueryOptions {}







public function metrics(bool $arg): QueryOptions {}







public function scopeName(string $arg): QueryOptions {}









public function scopeQualifier(string $arg): QueryOptions {}
}




interface QueryScanConsistency
{



public const NOT_BOUNDED = 1;




public const REQUEST_PLUS = 2;




public const STATEMENT_PLUS = 3;
}




interface QueryProfile
{



public const OFF = 1;




public const PHASES = 2;




public const TIMINGS = 3;
}

class ClusterOptions
{
public function credentials(string $username, string $password): ClusterOptions {}
}




