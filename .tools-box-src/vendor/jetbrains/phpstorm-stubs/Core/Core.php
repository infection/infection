<?php


use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Pure;






#[Pure]
function zend_version(): string {}







#[Pure]
function func_num_args(): int {}










#[Pure]
function func_get_arg(int $position): mixed {}







#[Pure]
function func_get_args(): array {}










#[Pure]
function strlen(string $string): int {}















#[Pure]
function strcmp(string $string1, string $string2): int {}


















#[Pure]
function strncmp(string $string1, string $string2, int $length): int {}















#[Pure]
function strcasecmp(string $string1, string $string2): int {}

















#[Pure]
function strncasecmp(string $string1, string $string2, int $length): int {}










#[Pure]
function str_starts_with(string $haystack, string $needle): bool {}










#[Pure]
function str_ends_with(string $haystack, string $needle): bool {}










#[Pure]
function str_contains(string $haystack, string $needle): bool {}

/**
@removed

















*/
#[Deprecated(reason: "Use a foreach loop instead", since: "7.2")]
function each(&$array): array {}



























































































































function error_reporting(?int $error_level): int {}


























function define(
string $constant_name,
#[LanguageLevelTypeAware(['8.1' => 'mixed'], default: 'null|array|bool|int|float|string')] $value,
#[PhpStormStubsElementAvailable(from: '5.3', to: '5.6')] bool $case_insensitive,
#[PhpStormStubsElementAvailable(from: '7.0')] #[Deprecated(since: 7.3)] bool $case_insensitive = false
): bool {}










#[Pure(true)]
function defined(string $constant_name): bool {}












#[Pure]
function get_class(object $object): string {}






#[Pure]
function get_called_class(): string {}
















#[Pure]
function get_parent_class(object|string $object_or_class): string|false {}














#[Pure]
function method_exists($object_or_class, string $method): bool {}













#[Pure]
function property_exists($object_or_class, string $property): bool {}









function trait_exists(string $trait, bool $autoload = true): bool {}













function class_exists(string $class, bool $autoload = true): bool {}














function interface_exists(string $interface, bool $autoload = true): bool {}














#[Pure(true)]
function function_exists(string $function): bool {}














function enum_exists(string $enum, bool $autoload = true): bool {}









function class_alias(string $class, string $alias, bool $autoload = true): bool {}















#[Pure(true)]
function get_included_files(): array {}






#[Pure(true)]
function get_required_files(): array {}


















#[Pure]
function is_subclass_of(mixed $object_or_class, string $class, bool $allow_string = true): bool {}

















#[Pure]
function is_a(mixed $object_or_class, string $class, bool $allow_string = false): bool {}












#[Pure]
function get_class_vars(string $class): array {}











#[Pure]
function get_object_vars(object $object): array {}










#[Pure]
function get_class_methods(object|string $object_or_class): array {}
















function trigger_error(string $message, int $error_level = E_USER_NOTICE): bool {}









function user_error(string $message, int $error_level = E_USER_NOTICE): bool {}



































function set_error_handler(?callable $callback, int $error_levels = E_ALL|E_STRICT) {}






#[LanguageLevelTypeAware(['8.2' => 'true'], default: 'bool')]
function restore_error_handler(): bool {}















function set_exception_handler(?callable $callback) {}






#[LanguageLevelTypeAware(['8.2' => 'true'], default: 'bool')]
function restore_exception_handler(): bool {}













#[Pure(true)]
function get_declared_classes(): array {}







#[Pure(true)]
function get_declared_interfaces(): array {}








#[Pure(true)]
function get_declared_traits(): array {}











#[Pure(true)]
function get_defined_functions(#[PhpStormStubsElementAvailable(from: '7.1')] bool $exclude_disabled = true): array {}






#[Pure(true)]
function get_defined_vars(): array {}

/**
@removed









*/
#[Deprecated(reason: "Use anonymous functions instead", since: "7.2")]
function create_function(string $args, string $code): false|string {}












function get_resource_type($resource): string {}










#[Pure]
function get_loaded_extensions(bool $zend_extensions = false): array {}

































#[Pure]
function extension_loaded(string $extension): bool {}













#[Pure]
function get_extension_funcs(string $extension): array|false {}























































#[Pure(true)]
function get_defined_constants(bool $categorize = false): array {}



































































































function debug_backtrace(int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT, int $limit = 0): array {}























function debug_print_backtrace(
int $options = 0,
#[PhpStormStubsElementAvailable(from: '7.0')] int $limit = 0
): void {}






function gc_collect_cycles(): int {}






#[Pure(true)]
function gc_enabled(): bool {}






function gc_enable(): void {}






function gc_disable(): void {}













#[ArrayShape(["runs" => "int", "collected" => "int", "threshold" => "int", "roots" => "int"])]
#[Pure(true)]
function gc_status(): array {}







function gc_mem_caches(): int {}















#[Pure(true)]
function get_resources(?string $type): array {}
