<?php





use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Pure;

define("ARRAY_FILTER_USE_BOTH", 1);




define("ARRAY_FILTER_USE_KEY", 2);







#[Pure]
function array_merge_recursive(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.3')] array $arr1,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.0')] array $arrays,
array ...$arrays
): array {}

















#[Pure]
function array_replace(
array $array,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.0')] $replacements,
array ...$replacements
): array {}












#[Pure]
function array_replace_recursive(
array $array,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.0')] $replacements,
array ...$replacements
): array {}















#[Pure]
function array_keys(array $array, mixed $filter_value, bool $strict = false): array {}

/**
@meta






*/
#[Pure]
function array_values(array $array): array {}










#[Pure]
function array_count_values(array $array): array {}











#[Pure]
function array_column(array $array, string|int|null $column_key, string|int|null $index_key = null): array {}

/**
@meta









*/
#[Pure]
function array_reverse(array $array, bool $preserve_keys = false): array {}

/**
@meta



























*/
function array_reduce(array $array, callable $callback, mixed $initial = null): mixed {}





















#[Pure]
function array_pad(array $array, int $length, mixed $value): array {}









#[Pure]
function array_flip(array $array): array {}

/**
@meta










*/
#[Pure]
function array_change_key_case(array $array, int $case = CASE_LOWER): array {}















function array_rand(array $array, int $num = 1): array|string|int {}

/**
@meta




























*/
#[Pure]
function array_unique(array $array, int $flags = SORT_STRING): array {}

/**
@meta








*/
#[Pure]
function array_intersect(array $array, #[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $arrays, array ...$arrays): array {}

/**
@meta









*/
#[Pure]
function array_intersect_key(array $array, #[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $arrays, array ...$arrays): array {}

/**
@meta














*/
function array_intersect_ukey(
array $array,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] array $array2,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] callable $key_compare_func,
#[PhpStormStubsElementAvailable(from: '8.0')] ...$rest
): array {}

/**
@meta




















*/
function array_uintersect(
array $array,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] array $array2,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] callable $data_compare_func,
#[PhpStormStubsElementAvailable(from: '8.0')] ...$rest
): array {}

/**
@meta








*/
#[Pure]
function array_intersect_assoc(array $array, #[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $arrays, array ...$arrays): array {}

/**
@meta


















*/
function array_uintersect_assoc(
array $array,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] array $array2,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] callable $data_compare_func,
#[PhpStormStubsElementAvailable(from: '8.0')] ...$rest
): array {}

/**
@meta














*/
function array_intersect_uassoc(
array $array,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] array $array2,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] callable $key_compare_func,
#[PhpStormStubsElementAvailable(from: '8.0')] ...$rest
): array {}

/**
@meta





















*/
#[Pure]
function array_uintersect_uassoc(
array $array,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] array $array2,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] callable $data_compare_func,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] callable $key_compare_func,
#[PhpStormStubsElementAvailable(from: '8.0')] ...$rest
): array {}

/**
@meta








*/
#[Pure]
function array_diff(array $array, #[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $arrays, array ...$arrays): array {}

/**
@meta











*/
#[Pure]
function array_diff_key(array $array, #[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $arrays, array ...$arrays): array {}

/**
@meta

















*/
function array_diff_ukey(
array $array,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] array $array2,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] callable $key_compare_func,
#[PhpStormStubsElementAvailable(from: '8.0')] ...$rest,
): array {}

/**
@meta




















*/
function array_udiff(
array $array,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] array $array2,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] callable $data_compare_func,
#[PhpStormStubsElementAvailable(from: '8.0')] ...$rest,
): array {}

/**
@meta










*/
#[Pure]
function array_diff_assoc(
array $array,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $arrays,
array ...$arrays
): array {}

/**
@meta



























*/
function array_udiff_assoc(
array $array,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] array $array2,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] callable $data_compare_func,
#[PhpStormStubsElementAvailable(from: '8.0')] ...$rest,
): array {}

/**
@meta

















*/
function array_diff_uassoc(
array $array,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] array $array2,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] callable $key_compare_func,
#[PhpStormStubsElementAvailable(from: '8.0')] ...$rest,
): array {}

/**
@meta


































*/
function array_udiff_uassoc(
array $array,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] array $array2,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] callable $data_compare_func,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] callable $key_compare_func,
#[PhpStormStubsElementAvailable(from: '8.0')] ...$rest
): array {}









#[Pure]
function array_sum(array $array): int|float {}









#[Pure]
function array_product(array $array): int|float {}

/**
@meta































*/
function array_filter(array $array, ?callable $callback, int $mode = 0): array {}

/**
@meta











*/
function array_map(
?callable $callback,
#[PhpStormStubsElementAvailable(from: '8.0')] array $array,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $arrays,
array ...$arrays
): array {}

















#[Pure]
function array_chunk(array $array, int $length, bool $preserve_keys = false): array {}

/**
@meta











*/
#[Pure]
#[LanguageLevelTypeAware(["8.0" => "array"], default: "array|false")]
function array_combine(array $keys, array $values) {}












#[Pure]
function array_key_exists($key, #[LanguageLevelTypeAware(["8.0" => "array"], default: "array|ArrayObject")] $array): bool {}











#[Pure]
function array_key_first(array $array): string|int|null {}











#[Pure]
function array_key_last(array $array): string|int|null {}








#[Pure]
function array_is_list(array $array): bool {}








#[Pure]
function pos(object|array $array): mixed {}









#[Pure]
function sizeof(Countable|array $value, int $mode = COUNT_NORMAL): int {}












#[Pure]
function key_exists($key, array $array): bool {}















function assert(
mixed $assertion,
#[PhpStormStubsElementAvailable(from: '7.0')] #[LanguageLevelTypeAware(['7.0' => 'Throwable|string|null'], default: 'string')] $description = null
): bool {}






class AssertionError extends Error {}





















































function assert_options(int $option, mixed $value): mixed {}



































function version_compare(
string $version1,
string $version2,
#[ExpectedValues(values: [
"<",
"lt",
"<=",
"le",
">",
"gt",
">=",
"ge",
"==",
"=",
"eq",
"!=",
"<>",
"ne"
])] ?string $operator
): int|bool {}













#[Pure(true)]
function ftok(string $filename, string $project_id): int {}









#[Pure]
function str_rot13(string $string): string {}







#[Pure(true)]
function stream_get_filters(): array {}








#[Pure]
function stream_isatty($stream): bool {}



















































































































function stream_filter_register(string $filter_name, string $class): bool {}







function stream_bucket_make_writeable($brigade): ?object {}








function stream_bucket_prepend($brigade, object $bucket): void {}








function stream_bucket_append($brigade, object $bucket): void {}








function stream_bucket_new($stream, string $buffer): object {}












function output_add_rewrite_var(string $name, string $value): bool {}






























function output_reset_rewrite_vars(): bool {}







function sys_get_temp_dir(): string {}










#[Pure(true)]
function realpath_cache_get(): array {}







#[Pure(true)]
function realpath_cache_size(): int {}









function get_mangled_object_vars(object $object): array {}


















#[Pure]
function get_debug_type(mixed $value): string {}








#[Pure]
function get_resource_id($resource): int {}
