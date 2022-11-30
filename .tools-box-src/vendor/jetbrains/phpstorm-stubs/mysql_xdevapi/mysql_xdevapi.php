<?php








namespace mysql_xdevapi;

define('MYSQLX_LOCK_DEFAULT', 0);
define('MYSQLX_TYPE_DECIMAL', 0);
define('MYSQLX_TYPE_TINY', 1);
define('MYSQLX_TYPE_SHORT', 2);
define('MYSQLX_TYPE_SMALLINT', 17);
define('MYSQLX_TYPE_MEDIUMINT', 18);
define('MYSQLX_TYPE_INT', 19);
define('MYSQLX_TYPE_BIGINT', 20);
define('MYSQLX_TYPE_LONG', 3);
define('MYSQLX_TYPE_FLOAT', 4);
define('MYSQLX_TYPE_DOUBLE', 5);
define('MYSQLX_TYPE_NULL', 6);
define('MYSQLX_TYPE_TIMESTAMP', 7);
define('MYSQLX_TYPE_LONGLONG', 8);
define('MYSQLX_TYPE_INT24', 9);
define('MYSQLX_TYPE_DATE', 10);
define('MYSQLX_TYPE_TIME', 11);
define('MYSQLX_TYPE_DATETIME', 12);
define('MYSQLX_TYPE_YEAR', 13);
define('MYSQLX_TYPE_NEWDATE', 14);
define('MYSQLX_TYPE_ENUM', 247);
define('MYSQLX_TYPE_SET', 248);
define('MYSQLX_TYPE_TINY_BLOB', 249);
define('MYSQLX_TYPE_MEDIUM_BLOB', 250);
define('MYSQLX_TYPE_LONG_BLOB', 251);
define('MYSQLX_TYPE_BLOB', 252);
define('MYSQLX_TYPE_VAR_STRING', 253);
define('MYSQLX_TYPE_STRING', 254);
define('MYSQLX_TYPE_CHAR', 1);
define('MYSQLX_TYPE_BYTES', 21);
define('MYSQLX_TYPE_INTERVAL', 247);
define('MYSQLX_TYPE_GEOMETRY', 255);
define('MYSQLX_TYPE_JSON', 245);
define('MYSQLX_TYPE_NEWDECIMAL', 246);
define('MYSQLX_TYPE_BIT', 16);
define('MYSQLX_LOCK_NOWAIT', 1);
define('MYSQLX_LOCK_SKIP_LOCKED', 2);






function getSession($uri) {}






function expression($expression) {}





interface BaseResult
{





public function getWarnings(): array;






public function getWarningsCount(): int;
}






class Collection
{




public function __construct() {}







public function add($document): CollectionAdd {}








public function addOrReplaceOne($id, $document): Result {}






public function count(): int {}







public function createIndex($index_name, $index_desc_json) {}







public function dropIndex($index_name): bool {}






public function existsInDatabase(): bool {}






public function find($search_condition): CollectionFind {}






public function getName(): string {}








public function getOne($id) {}






public function getSchema(): Schema {}






public function getSession(): Session {}







public function modify($search_condition): CollectionModify {}







public function remove($search_condition): CollectionRemove {}








public function removeOne($id): Result {}








public function replaceOne($id, $doc): Result {}
}





class CollectionAdd implements \mysql_xdevapi\Executable
{



public function execute(): Result {}
}






class CollectionFind implements \mysql_xdevapi\Executable, \mysql_xdevapi\CrudOperationBindable, \mysql_xdevapi\CrudOperationLimitable, \mysql_xdevapi\CrudOperationSortable
{







public function bind(array $placeholder_values): CollectionFind {}






public function execute(): DocResult {}







public function fields(string $projection): CollectionFind {}







public function groupBy(string $sort_expr): CollectionFind {}







public function having(string $sort_expr): CollectionFind {}







public function limit(int $rows): CollectionFind {}












public function lockExclusive(int $lock_waiting_option): CollectionFind {}














public function lockShared(int $lock_waiting_option): CollectionFind {}












public function offset(int $position): CollectionFind {}











public function sort(string $sort_expr): CollectionFind {}
}





class CollectionModify implements \mysql_xdevapi\Executable, \mysql_xdevapi\CrudOperationBindable, \mysql_xdevapi\CrudOperationLimitable, \mysql_xdevapi\CrudOperationSkippable, \mysql_xdevapi\CrudOperationSortable
{







public function arrayAppend(string $collection_field, string $expression_or_literal): CollectionModify {}








public function arrayInsert(string $collection_field, string $expression_or_literal): CollectionModify {}








public function bind(array $placeholder_values): CollectionModify {}






public function execute(): Result {}







public function limit(int $rows): CollectionModify {}







public function patch(string $document): CollectionModify {}








public function replace(string $collection_field, string $expression_or_literal): CollectionModify {}








public function set(string $collection_field, string $expression_or_literal): CollectionModify {}







public function skip(int $position): CollectionModify {}







public function sort(string $sort_expr): CollectionModify {}







public function unset(array $fields): CollectionModify {}
}

class CollectionRemove implements \mysql_xdevapi\Executable, \mysql_xdevapi\CrudOperationBindable, \mysql_xdevapi\CrudOperationLimitable, \mysql_xdevapi\CrudOperationSortable
{







public function bind(array $placeholder_values): CollectionRemove {}






public function execute(): Result {}







public function limit(int $rows): CollectionRemove {}







public function sort(string $sort_expr): CollectionRemove {}
}





class ColumnResult
{





public function getCharacterSetName(): string {}






public function getCollationName(): string {}






public function getColumnLabel(): string {}






public function getColumnName(): string {}






public function getFractionalDigits(): int {}






public function getLength(): int {}






public function getSchemaName(): string {}






public function getTableLabel(): string {}






public function getTableName(): string {}






public function getType(): int {}






public function isNumberSigned(): int {}






public function isPadded(): int {}
}






interface CrudOperationBindable
{






public function bind(array $placeholder_values): CrudOperationBindable;
}






interface CrudOperationLimitable
{






public function limit(int $rows): CrudOperationLimitable;
}






interface CrudOperationSkippable
{






public function skip(int $skip): CrudOperationSkippable;
}






interface CrudOperationSortable
{






public function sort(string $sort_expr): CrudOperationSortable;
}






interface DatabaseObject
{





public function existsInDatabase(): bool;






public function getName(): string;






public function getSession(): Session;
}






class DocResult implements \mysql_xdevapi\BaseResult, \Traversable
{





public function fetchAll(): array {}






public function fetchOne(): array {}






public function getWarnings(): array {}






public function getWarningsCount(): int {}
}





class Exception extends \RuntimeException implements \Throwable {}






interface Executable
{





public function execute(): Result;
}






class ExecutionStatus
{
public $affectedItems;
public $matchedItems;
public $foundItems;
public $lastInsertId;
public $lastDocumentId;
}






class Expression
{

public $name;


public function __construct(string $expression) {}
}






class Result implements \mysql_xdevapi\BaseResult, \Traversable
{





public function getAutoIncrementValue(): int {}






public function getGeneratedIds(): array {}






public function getWarnings(): array {}






public function getWarningsCount(): int {}
}






class RowResult implements \mysql_xdevapi\BaseResult, \Traversable
{





public function fetchAll(): array {}






public function fetchOne(): array {}






public function getColumnsCount(): int {}






public function getColumnNames(): array {}






public function getColumns(): array {}






public function getWarnings(): array {}






public function getWarningsCount(): int {}
}






class Schema implements \mysql_xdevapi\DatabaseObject
{

public $name;








public function createCollection(string $name): Collection {}







public function dropCollection(string $collection_name): bool {}







public function existsInDatabase(): bool {}







public function getCollection(string $name): Collection {}







public function getCollectionAsTable(string $name): Table {}






public function getCollections(): array {}






public function getName(): string {}






public function getSession(): Session {}







public function getTable(string $name): Table {}






public function getTables(): array {}
}






interface SchemaObject extends \mysql_xdevapi\DatabaseObject
{

public function getSchema(): Schema;
}






class Session
{





public function close(): bool {}






public function commit(): object {}







public function createSchema(string $schema_name): Schema {}







public function dropSchema(string $schema_name): bool {}






public function generateUUID(): string {}







public function getSchema(string $schema_name): Schema {}






public function getSchemas(): array {}






public function getServerVersion(): int {}






public function listClients(): array {}








public function quoteName(string $name): string {}






public function releaseSavepoint(string $name): void {}





public function rollback(): void {}






public function rollbackTo(string $name): void {}







public function setSavepoint(string $name): string {}







public function sql(string $query): SqlStatement {}





public function startTransaction(): void {}
}






class SqlStatement
{

public const EXECUTE_ASYNC = 1;
public const BUFFERED = 2;


public $statement;








public function bind(string $param): SqlStatement {}






public function execute(): Result {}






public function getNextResult(): Result {}






public function getResult(): Result {}






public function hasMoreResults(): bool {}
}






class SqlStatementResult implements \mysql_xdevapi\BaseResult, \Traversable
{






public function fetchAll(): array {}






public function fetchOne(): array {}






public function getAffectedItemsCount(): int {}






public function getColumnsCount(): int {}






public function getColumnNames(): array {}






public function getColumns(): array {}






public function getGeneratedIds(): array {}






public function getLastInsertId(): string {}






public function getWarnings(): array {}






public function getWarningCounts(): int {}






public function hasData(): bool {}






public function nextResult(): Result {}
}






class Statement
{

public const EXECUTE_ASYNC = 1;
public const BUFFERED = 2;







public function getNextResult(): Result {}






public function getResult(): Result {}






public function hasMoreResults(): bool {}
}






class Table implements \mysql_xdevapi\SchemaObject
{

public $name;







public function count(): int {}






public function delete(): TableDelete {}






public function existsInDatabase(): bool {}






public function getName(): string {}






public function getSchema(): Schema {}






public function getSession(): Session {}








public function insert($columns, ...$additionalColumns): TableInsert {}






public function isView(): bool {}








public function select($columns, ...$additionalColumns): TableSelect {}






public function update(): TableUpdate {}
}






class TableDelete implements \mysql_xdevapi\Executable
{







public function bind(array $placeholder_values): TableDelete {}






public function execute(): Result {}







public function limit(int $rows): TableDelete {}







public function orderby(string $orderby_expr): TableDelete {}







public function where(string $where_expr): TableDelete {}
}






class TableInsert implements \mysql_xdevapi\Executable
{





public function execute(): Result {}







public function values(array $row_values): TableInsert {}
}






class TableSelect implements \mysql_xdevapi\Executable
{






public function bind(array $placeholder_values): TableSelect {}






public function execute(): RowResult {}







public function groupBy($sort_expr): TableSelect {}







public function having(string $sort_expr): TableSelect {}







public function limit(int $rows): TableSelect {}







public function lockExclusive(?int $lock_waiting_option): TableSelect {}







public function lockShared(?int $lock_waiting_option): TableSelect {}







public function offset(int $position): TableSelect {}







public function orderby(...$sort_expr): TableSelect {}







public function where(string $where_expr): TableSelect {}
}






class TableUpdate implements \mysql_xdevapi\Executable
{






public function bind(array $placeholder_values): TableUpdate {}






public function execute(): TableUpdate {}







public function limit(int $rows): TableUpdate {}







public function orderby(...$orderby_expr): TableUpdate {}








public function set(string $table_field, string $expression_or_literal): TableUpdate {}







public function where(string $where_expr): TableUpdate {}
}






class Warning
{

public $message;
public $level;
public $code;


private function __construct() {}
}






class XSession {}
