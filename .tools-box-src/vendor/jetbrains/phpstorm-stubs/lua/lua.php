<?php







class Lua
{





public const LUA_VERSION = '5.1.4';





public function __construct(?string $lua_script_file = null) {}









public function assign(string $name, $value) {}










public function call(callable $lua_func, array $args = [], bool $use_self = false) {}








public function eval(string $statements) {}








public function include(string $file) {}






public function getVersion(): string {}









public function registerCallback(string $name, callable $function) {}
}
