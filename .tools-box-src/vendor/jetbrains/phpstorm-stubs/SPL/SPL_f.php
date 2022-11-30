<?php


use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Pure;






#[Pure]
function spl_classes(): array {}














function spl_autoload(string $class, ?string $file_extensions): void {}















function spl_autoload_extensions(?string $file_extensions): string {}

















function spl_autoload_register(?callable $callback, bool $throw = true, bool $prepend = false): bool {}










function spl_autoload_unregister(callable $callback): bool {}









#[LanguageLevelTypeAware(["8.0" => "array"], default: "array|false")]
function spl_autoload_functions() {}










function spl_autoload_call(string $class): void {}














#[Pure]
function class_parents($object_or_class, bool $autoload = true): array|false {}














#[Pure]
function class_implements($object_or_class, bool $autoload = true): array|false {}








#[Pure]
function spl_object_hash(object $object): string {}












function iterator_to_array(Traversable $iterator, bool $preserve_keys = true): array {}









#[Pure]
function iterator_count(Traversable $iterator): int {}

















function iterator_apply(Traversable $iterator, callable $callback, ?array $args): int {}













function class_uses($object_or_class, bool $autoload = true): array|false {}







function spl_object_id(object $object): int {}
