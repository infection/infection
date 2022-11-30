<?php


use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Internal\TentativeType;





class SQLite3
{
public const OK = 0;
public const DENY = 1;
public const IGNORE = 2;
public const CREATE_INDEX = 1;
public const CREATE_TABLE = 2;
public const CREATE_TEMP_INDEX = 3;
public const CREATE_TEMP_TABLE = 4;
public const CREATE_TEMP_TRIGGER = 5;
public const CREATE_TEMP_VIEW = 6;
public const CREATE_TRIGGER = 7;
public const CREATE_VIEW = 8;
public const DELETE = 9;
public const DROP_INDEX = 10;
public const DROP_TABLE = 11;
public const DROP_TEMP_INDEX = 12;
public const DROP_TEMP_TABLE = 13;
public const DROP_TEMP_TRIGGER = 14;
public const DROP_TEMP_VIEW = 15;
public const DROP_TRIGGER = 16;
public const DROP_VIEW = 17;
public const INSERT = 18;
public const PRAGMA = 19;
public const READ = 20;
public const SELECT = 21;
public const TRANSACTION = 22;
public const UPDATE = 23;
public const ATTACH = 24;
public const DETACH = 25;
public const ALTER_TABLE = 26;
public const REINDEX = 27;
public const ANALYZE = 28;
public const CREATE_VTABLE = 29;
public const DROP_VTABLE = 30;
public const FUNCTION = 31;
public const SAVEPOINT = 32;
public const COPY = 0;
public const RECURSIVE = 33;





















#[TentativeType]
public function open(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $filename,
#[PhpStormStubsElementAvailable(from: '5.3', to: '5.6')] $flags,
#[PhpStormStubsElementAvailable(from: '5.3', to: '5.6')] $encryptionKey,
#[PhpStormStubsElementAvailable(from: '7.0')] #[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = SQLITE3_OPEN_READWRITE|SQLITE3_OPEN_CREATE,
#[PhpStormStubsElementAvailable(from: '7.0')] #[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $encryptionKey = null
): void {}






public function close() {}










#[TentativeType]
public function exec(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $query): bool {}







#[ArrayShape(["versionString" => "string", "versionNumber" => "int"])]
#[TentativeType]
public static function version(): array {}






#[TentativeType]
public function lastInsertRowID(): int {}







#[TentativeType]
public function lastErrorCode(): int {}






#[TentativeType]
public function lastErrorMsg(): string {}











#[TentativeType]
public function busyTimeout(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $milliseconds): bool {}










#[TentativeType]
public function loadExtension(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name): bool {}









#[TentativeType]
public function changes(): int {}










#[TentativeType]
public static function escapeString(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $string): string {}









#[TentativeType]
public function prepare(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $query): SQLite3Stmt|false {}









#[TentativeType]
public function query(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $query): SQLite3Result|false {}
























#[TentativeType]
public function querySingle(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $query,
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $entireRow = false
): mixed {}






















#[TentativeType]
public function createFunction(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'callable'], default: '')] $callback,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $argCount = -1,
#[PhpStormStubsElementAvailable(from: '7.1')] int $flags = 0
): bool {}























#[TentativeType]
public function createAggregate(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'callable'], default: '')] $stepCallback,
#[LanguageLevelTypeAware(['8.0' => 'callable'], default: '')] $finalCallback,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $argCount = -1
): bool {}

















#[TentativeType]
public function createCollation(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name, callable $callback): bool {}












public function openBlob(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $table,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $column,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $rowid,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $database = 'main',
#[PhpStormStubsElementAvailable(from: '7.2')] int $flags = SQLITE3_OPEN_READONLY
) {}







#[TentativeType]
public function enableExceptions(
#[PhpStormStubsElementAvailable(from: '5.3', to: '5.6')] $enable,
#[PhpStormStubsElementAvailable(from: '7.0')] #[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $enable = false
): bool {}




















public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $filename,
#[PhpStormStubsElementAvailable(from: '5.3', to: '5.6')] $flags,
#[PhpStormStubsElementAvailable(from: '5.3', to: '5.6')] $encryptionKey,
#[PhpStormStubsElementAvailable(from: '7.0')] #[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = SQLITE3_OPEN_READWRITE|SQLITE3_OPEN_CREATE,
#[PhpStormStubsElementAvailable(from: '7.0')] #[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $encryptionKey = null
) {}





#[TentativeType]
public function lastExtendedErrorCode(): int {}





#[TentativeType]
public function enableExtendedResultCodes(
#[PhpStormStubsElementAvailable(from: '7.4', to: '7.4')] bool $enable,
#[PhpStormStubsElementAvailable(from: '8.0')] bool $enable = true
): bool {}








#[TentativeType]
public function backup(SQLite3 $destination, string $sourceDatabase = 'main', string $destinationDatabase = 'main'): bool {}






#[TentativeType]
public function setAuthorizer(?callable $callback): bool {}
}





class SQLite3Stmt
{





#[TentativeType]
public function paramCount(): int {}






#[TentativeType]
public function close(): bool {}






#[TentativeType]
public function reset(): bool {}







#[TentativeType]
public function clear(): bool {}







#[TentativeType]
public function execute(): SQLite3Result|false {}






















#[TentativeType]
public function bindParam(
#[LanguageLevelTypeAware(['8.0' => 'string|int'], default: '')] $param,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] &$var,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $type = SQLITE3_TEXT
): bool {}






















#[TentativeType]
public function bindValue(
#[LanguageLevelTypeAware(['8.0' => 'string|int'], default: '')] $param,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $value,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $type = SQLITE3_TEXT
): bool {}

#[TentativeType]
public function readOnly(): bool {}





private function __construct(
#[LanguageLevelTypeAware(['8.0' => 'SQLite3'], default: '')] $sqlite3,
#[PhpStormStubsElementAvailable(from: '8.0')] string $query
) {}








#[TentativeType]
public function getSQL(bool $expand = false): string|false {}
}





class SQLite3Result
{





#[TentativeType]
public function numColumns(): int {}










#[TentativeType]
public function columnName(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $column): string|false {}













#[TentativeType]
public function columnType(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $column): int|false {}
















#[TentativeType]
public function fetchArray(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $mode = SQLITE3_BOTH): array|false {}







#[TentativeType]
public function reset(): bool {}






public function finalize() {}

private function __construct() {}
}







define('SQLITE3_ASSOC', 1);







define('SQLITE3_NUM', 2);







define('SQLITE3_BOTH', 3);





define('SQLITE3_INTEGER', 1);





define('SQLITE3_FLOAT', 2);





define('SQLITE3_TEXT', 3);





define('SQLITE3_BLOB', 4);





define('SQLITE3_NULL', 5);





define('SQLITE3_OPEN_READONLY', 1);





define('SQLITE3_OPEN_READWRITE', 2);






define('SQLITE3_OPEN_CREATE', 4);


