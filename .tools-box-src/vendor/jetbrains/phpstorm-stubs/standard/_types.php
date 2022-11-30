<?php

namespace {
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;















function PS_UNRESERVE_PREFIX_array(...$_) {}








function PS_UNRESERVE_PREFIX_list($var1, ...$_) {}

















function PS_UNRESERVE_PREFIX_die($status = "") {}

















function PS_UNRESERVE_PREFIX_exit($status = "") {}




























function PS_UNRESERVE_PREFIX_empty($var) {}












function PS_UNRESERVE_PREFIX_isset($var, ...$_) {}









function PS_UNRESERVE_PREFIX_unset($var, ...$_) {}































function PS_UNRESERVE_PREFIX_eval($code) {}

/**
@template
@template
@template
@template
@template-implements





*/
final class Generator implements Iterator
{




public function rewind(): void {}





public function valid(): bool {}





public function current(): mixed {}





#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: 'string|float|int|bool|null')]
public function key() {}





public function next(): void {}






public function send(mixed $value): mixed {}






public function PS_UNRESERVE_PREFIX_throw(Throwable $exception): mixed {}








public function getReturn(): mixed {}







public function __wakeup() {}
}

class ClosedGeneratorException extends Exception {}
}

namespace ___PHPSTORM_HELPERS {
class PS_UNRESERVE_PREFIX_this {}

class PS_UNRESERVE_PREFIX_static {}

class object
{











public function __construct() {}

















public function __destruct() {}









public function __call(string $name, array $arguments) {}









public static function __callStatic(string $name, array $arguments) {}








public function __get(string $name) {}









public function __set(string $name, $value): void {}








public function __isset(string $name): bool {}








public function __unset(string $name): void {}












public function __sleep(): array {}










public function __wakeup(): void {}







public function __toString(): string {}







public function __invoke() {}








public function __debugInfo(): ?array {}









public static function __set_state(array $an_array): object {}











public function __clone(): void {}






public function __serialize(): array {}







public function __unserialize(array $data): void {}
}
}
