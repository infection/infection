<?php

use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\TentativeType;










interface SessionHandlerInterface
{









#[TentativeType]
public function close(): bool;











#[TentativeType]
public function destroy(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $id): bool;














#[LanguageLevelTypeAware(['7.1' => 'int|false'], default: 'bool')]
#[TentativeType]
public function gc(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $max_lifetime): int|false;












#[TentativeType]
public function open(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $path,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name
): bool;












#[TentativeType]
public function read(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $id): string|false;


















#[TentativeType]
public function write(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $id,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $data
): bool;
}






interface SessionIdInterface
{







#[TentativeType]
public function create_sid(): string;
}








interface SessionUpdateTimestampHandlerInterface
{








#[TentativeType]
public function validateId(string $id): bool;














#[TentativeType]
public function updateTimestamp(string $id, string $data): bool;
}
















class SessionHandler implements SessionHandlerInterface, SessionIdInterface
{









#[TentativeType]
public function close(): bool {}







#[TentativeType]
public function create_sid(): string {}











#[TentativeType]
public function destroy(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $id): bool {}














#[TentativeType]
public function gc(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $max_lifetime): int|false {}












#[TentativeType]
public function open(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $path,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name
): bool {}












#[TentativeType]
public function read(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $id): string|false {}


















#[TentativeType]
public function write(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $id,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $data
): bool {}








public function validateId($session_id) {}













public function updateTimestamp($session_id, $session_data) {}
}
