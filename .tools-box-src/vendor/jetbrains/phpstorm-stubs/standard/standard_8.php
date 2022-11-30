<?php

use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Pure;























































#[LanguageLevelTypeAware(['8.2' => 'true'], default: 'bool')]
function syslog(int $priority, string $message): bool {}






#[LanguageLevelTypeAware(['8.2' => 'true'], default: 'bool')]
function closelog(): bool {}









function header_register_callback(callable $callback): bool {}





















function getimagesizefromstring(string $string, &$image_info): array|false {}










#[LanguageLevelTypeAware(["8.0" => "int"], default: "int|false")]
function stream_set_chunk_size($stream, int $size) {}

/**
@removed



*/
#[Deprecated(since: '5.3')]
function define_syslog_variables() {}






function lcg_value(): float {}













#[Pure]
#[LanguageLevelTypeAware(["8.0" => "string"], default: "string|false")]
function metaphone(string $string, int $max_phonemes = 0): false|string {}


































































function ob_start($callback, int $chunk_size = 0, int $flags = PHP_OUTPUT_HANDLER_STDFLAGS): bool {}






function ob_flush(): bool {}






function ob_clean(): bool {}








function ob_end_flush(): bool {}








function ob_end_clean(): bool {}






function ob_get_flush(): string|false {}







function ob_get_clean(): string|false {}







function ob_get_length(): int|false {}







function ob_get_level(): int {}






































































function ob_get_status(bool $full_status = false): array {}







#[Pure(true)]
function ob_get_contents(): string|false {}










function ob_implicit_flush(#[LanguageLevelTypeAware(["8.0" => "bool"], default: "int")] $enable = true): void {}










function ob_list_handlers(): array {}














#[LanguageLevelTypeAware(['8.2' => 'true'], default: 'bool')]
function ksort(array &$array, int $flags = SORT_REGULAR): bool {}














#[LanguageLevelTypeAware(['8.2' => 'true'], default: 'bool')]
function krsort(array &$array, int $flags = SORT_REGULAR): bool {}









function natsort(array &$array): bool {}









function natcasesort(array &$array): bool {}














#[LanguageLevelTypeAware(['8.2' => 'true'], default: 'bool')]
function asort(array &$array, int $flags = SORT_REGULAR): bool {}














#[LanguageLevelTypeAware(['8.2' => 'true'], default: 'bool')]
function arsort(array &$array, int $flags = SORT_REGULAR): bool {}

















#[LanguageLevelTypeAware(['8.2' => 'true'], default: 'bool')]
function sort(array &$array, int $flags = SORT_REGULAR): bool {}














function rsort(array &$array, int $flags = SORT_REGULAR): bool {}














#[LanguageLevelTypeAware(['8.2' => 'true'], default: 'bool')]
function usort(array &$array, callable $callback): bool {}













#[LanguageLevelTypeAware(['8.2' => 'true'], default: 'bool')]
function uasort(array &$array, callable $callback): bool {}




















#[LanguageLevelTypeAware(['8.2' => 'true'], default: 'bool')]
function uksort(array &$array, callable $callback): bool {}









#[LanguageLevelTypeAware(['8.2' => 'true'], default: 'bool')]
function shuffle(array &$array): bool {}

































#[LanguageLevelTypeAware(['8.2' => 'true'], default: 'bool')]
function array_walk(object|array &$array, callable $callback, mixed $arg): bool {}



























#[LanguageLevelTypeAware(['8.2' => 'true'], default: 'bool')]
function array_walk_recursive(object|array &$array, callable $callback, mixed $arg): bool {}




























#[Pure]
function count(Countable|array $value, int $mode = COUNT_NORMAL): int {}

/**
@meta









*/
function end(object|array &$array): mixed {}

/**
@meta








*/
function prev(object|array &$array): mixed {}

/**
@meta







*/
function next(object|array &$array): mixed {}

/**
@meta







*/
function reset(object|array &$array): mixed {}

/**
@meta










*/
#[Pure]
function current(object|array $array): mixed {}













#[Pure]
function key(object|array $array): string|int|null {}









#[Pure]
function min(
#[PhpStormStubsElementAvailable(from: '8.0')] mixed $value,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] mixed $values,
mixed ...$values
): mixed {}









#[Pure]
function max(
#[PhpStormStubsElementAvailable(from: '8.0')] mixed $value,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] mixed $values,
mixed ...$values
): mixed {}























#[Pure]
function in_array(mixed $needle, array $haystack, bool $strict = false): bool {}





























#[Pure]
function array_search(mixed $needle, array $haystack, bool $strict = false): string|int|false {}



























function extract(
array &$array,
#[ExpectedValues(flags: [
EXTR_OVERWRITE,
EXTR_SKIP,
EXTR_PREFIX_SAME,
EXTR_PREFIX_ALL,
EXTR_PREFIX_INVALID,
EXTR_IF_EXISTS,
EXTR_PREFIX_IF_EXISTS,
EXTR_REFS
])] int $flags = EXTR_OVERWRITE,
string $prefix = ""
): int {}














#[Pure]
function compact(#[PhpStormStubsElementAvailable(from: '8.0')] $var_name, #[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $var_names, ...$var_names): array {}
















#[Pure]
function array_fill(int $start_index, int $count, mixed $value): array {}













#[Pure]
function array_fill_keys(array $keys, mixed $value): array {}



















#[Pure]
function range($start, $end, int|float $step = 1): array {}














function array_multisort(
&$array,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $sort_order = SORT_ASC,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] $sort_flags = SORT_REGULAR,
&...$rest
): bool {}














function array_push(
array &$array,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.2')] $values,
mixed ...$values
): int {}

/**
@meta








*/
function array_pop(array &$array): mixed {}

/**
@meta







*/
function array_shift(array &$array): mixed {}














function array_unshift(array &$array, #[PhpStormStubsElementAvailable(from: '5.3', to: '7.2')] $values, mixed ...$values): int {}













































function array_splice(array &$array, int $offset, ?int $length, mixed $replacement = []): array {}

/**
@meta


























*/
#[Pure]
function array_slice(array $array, int $offset, ?int $length, bool $preserve_keys = false): array {}

/**
@meta







*/
#[Pure]
function array_merge(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.3')] $array,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.0')] $arrays,
array ...$arrays
): array {}
