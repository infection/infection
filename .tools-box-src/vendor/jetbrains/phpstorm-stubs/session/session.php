<?php


use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;





















#[LanguageLevelTypeAware(['8.0' => 'string|false'], default: 'string')]
function session_name(#[LanguageLevelTypeAware(['8.0' => 'null|string'], default: 'string')] $name) {}











#[LanguageLevelTypeAware(['8.0' => 'string|false'], default: 'string')]
function session_module_name(#[LanguageLevelTypeAware(['8.0' => 'null|string'], default: 'string')] $module) {}


















#[LanguageLevelTypeAware(['8.0' => 'string|false'], default: 'string')]
function session_save_path(#[LanguageLevelTypeAware(['8.0' => 'null|string'], default: 'string')] $path) {}




















#[LanguageLevelTypeAware(['8.0' => 'string|false'], default: 'string')]
function session_id(#[LanguageLevelTypeAware(['8.0' => 'null|string'], default: 'string')] $id) {}









function session_regenerate_id(bool $delete_old_session = false): bool {}







function session_register_shutdown(): void {}









function session_decode(string $data): bool {}

/**
@removed








*/
#[Deprecated(since: '5.3')]
function session_register(mixed $name, ...$_): bool {}

/**
@removed






*/
#[Deprecated(since: '5.3')]
function session_unregister(string $name): bool {}

/**
@removed








*/
#[Deprecated(since: '5.3')]
function session_is_registered(string $name): bool {}






#[LanguageLevelTypeAware(["8.0" => "string|false"], default: "string")]
function session_encode() {}









function session_start(#[PhpStormStubsElementAvailable(from: '7.0')] array $options = []): bool {}











#[LanguageLevelTypeAware(["8.0" => "string|false"], default: "string")]
function session_create_id(string $prefix = '') {}






#[LanguageLevelTypeAware(["8.0" => "int|false"], default: "int")]
function session_gc() {}






function session_destroy(): bool {}






#[LanguageLevelTypeAware(["7.2" => "bool"], default: "void")]
function session_unset() {}

















































function session_set_save_handler(callable $open, callable $close, callable $read, callable $write, callable $destroy, callable $gc, ?callable $create_sid = null, ?callable $validate_sid = null, ?callable $update_timestamp = null): bool {}











function session_set_save_handler(SessionHandlerInterface $sessionhandler, bool $register_shutdown = true): bool {}
























































#[LanguageLevelTypeAware(["8.0" => "string|false"], default: "string")]
function session_cache_limiter(#[LanguageLevelTypeAware(['8.0' => 'null|string'], default: 'string')] $value) {}
















#[LanguageLevelTypeAware(["8.0" => "int|false"], default: "int")]
function session_cache_expire(#[LanguageLevelTypeAware(['8.0' => 'null|int'], default: 'int')] $value) {}
















function session_set_cookie_params(array $lifetime_or_options): bool {}





























#[LanguageLevelTypeAware(["7.2" => "bool"], default: "void")]
function session_set_cookie_params(int $lifetime_or_options, ?string $path = null, ?string $domain = null, ?bool $secure = null, ?bool $httponly = null) {}

















#[ArrayShape(["lifetime" => "int", "path" => "string", "domain" => "string", "secure" => "bool", "httponly" => "bool", "samesite" => "string"])]
function session_get_cookie_params(): array {}






#[LanguageLevelTypeAware(["7.2" => "bool"], default: "void")]
function session_write_close() {}






#[LanguageLevelTypeAware(["7.2" => "bool"], default: "void")]
function session_commit() {}










function session_status(): int {}








#[LanguageLevelTypeAware(["7.2" => "bool"], default: "void")]
function session_abort() {}








#[LanguageLevelTypeAware(["7.2" => "bool"], default: "void")]
function session_reset() {}


