<?php

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Internal\ReturnTypeContract as TypeContract;
use JetBrains\PhpStorm\Pure;








#[Pure(true)]
function getlastmod(): int|false {}














#[Pure]
function base64_decode(string $string, bool $strict = false): string|false {}









#[Pure]
function base64_encode(string $string): string {}









#[Pure]
function convert_uuencode(string $string): string {}









#[Pure]
function convert_uudecode(string $string): string|false {}













#[Pure]
function abs(int|float $num): int|float {}













#[Pure]
#[LanguageLevelTypeAware(["8.0" => "float"], default: "float|false")]
function ceil(int|float $num) {}











#[Pure]
#[LanguageLevelTypeAware(["8.0" => "float"], default: "float|false")]
function floor(int|float $num) {}




















#[Pure]
function round(int|float $num, int $precision = 0, int $mode = PHP_ROUND_HALF_UP): float {}









#[Pure]
function sin(float $num): float {}









#[Pure]
function cos(float $num): float {}









#[Pure]
function tan(float $num): float {}









#[Pure]
function asin(float $num): float {}









#[Pure]
function acos(float $num): float {}









#[Pure]
function atan(float $num): float {}









#[Pure]
function atanh(float $num): float {}













#[Pure]
function atan2(float $y, float $x): float {}









#[Pure]
function sinh(float $num): float {}









#[Pure]
function cosh(float $num): float {}









#[Pure]
function tanh(float $num): float {}









#[Pure]
function asinh(float $num): float {}









#[Pure]
function acosh(float $num): float {}










#[Pure]
function expm1(float $num): float {}










#[Pure]
function log1p(float $num): float {}






#[Pure]
function pi(): float {}











#[Pure]
function is_finite(float $num): bool {}










#[Pure]
function is_nan(float $num): bool {}











#[Pure]
function intdiv(int $num1, int $num2): int {}









#[Pure]
function is_infinite(float $num): bool {}















#[Pure]
function pow(mixed $num, mixed $exponent): object|int|float {}









#[Pure]
function exp(float $num): float {}















#[Pure]
function log(float $num, float $base = M_E): float {}









#[Pure]
function log10(float $num): float {}










#[Pure]
function sqrt(float $num): float {}












#[Pure]
function hypot(float $x, float $y): float {}









#[Pure]
function deg2rad(float $num): float {}









#[Pure]
function rad2deg(float $num): float {}









#[Pure]
function bindec(string $binary_string): int|float {}









#[Pure]
function hexdec(string $hex_string): int|float {}









#[Pure]
function octdec(string $octal_string): int|float {}





















































































































#[Pure]
function decbin(int $num): string {}









#[Pure]
function decoct(int $num): string {}









#[Pure]
function dechex(int $num): string {}















#[Pure]
function base_convert(string $num, int $from_base, int $to_base): string {}














#[Pure]
function number_format(float $num, int $decimals = 0, ?string $decimal_separator = '.', ?string $thousands_separator = ','): string {}














#[Pure]
function fmod(float $num1, float $num2): float {}










#[Pure]
function fdiv(float $num1, float $num2): float {}









#[Pure]
function inet_ntop(string $ip): string|false {}










#[Pure]
function inet_pton(string $ip): string|false {}










#[Pure]
function ip2long(string $ip): int|false {}









#[Pure]
function long2ip(int $ip): string|false {}














#[Pure(true)]
function getenv(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.0')] $varname,
#[PhpStormStubsElementAvailable(from: '7.1')] ?string $name = null,
#[PhpStormStubsElementAvailable(from: '5.6')] bool $local_only = false
): array|string|false {}









function putenv(string $assignment): bool {}




















function getopt(
string $short_options,
array $long_options = [],
#[PhpStormStubsElementAvailable(from: '7.1')] &$rest_index
): array|false {}








#[Pure(true)]
function sys_getloadavg(): array|false {}

















#[Pure(true)]
function microtime(#[TypeContract(true: "float", false: "string")] bool $as_float = false): string|float {}

















#[Pure(true)]
#[ArrayShape(["sec" => "int", "usec" => "int", "minuteswest" => "int", "dsttime" => "int"])]
function gettimeofday(#[TypeContract(true: "float", false: "int[]")] bool $as_float = false): array|float {}











#[Pure(true)]
function getrusage(int $mode = 0): array|false {}





















function uniqid(string $prefix = "", bool $more_entropy = false): string {}









#[Pure]
function quoted_printable_decode(string $string): string {}









#[Pure]
function quoted_printable_encode(string $string): string {}

/**
@removed















*/
#[Pure]
#[Deprecated(since: '7.4', reason: 'Us mb_convert_string(), iconv() or UConverter instead.')]
function convert_cyr_string(string $str, string $from, string $to): string {}






#[Pure(true)]
function get_current_user(): string {}










function set_time_limit(int $seconds): bool {}










#[Pure]
function get_cfg_var(string $option): array|string|false {}

/**
@removed




*/
#[Deprecated(since: '5.3')]
function magic_quotes_runtime(bool $new_setting) {}

/**
@removed






*/
#[Deprecated(reason: "This function has been DEPRECATED as of PHP 5.4.0. Raises an E_CORE_ERROR", since: "5.3")]
function set_magic_quotes_runtime(bool $new_setting): bool {}

/**
@removed



*/
#[Deprecated(since: '7.4')]
function get_magic_quotes_gpc(): int {}






#[Deprecated(since: '7.4')]
function get_magic_quotes_runtime(): int {}

/**
@removed






























*/
#[Deprecated(reason: "This function has been DEPRECATED as of PHP 5.3.0", since: "5.3")]
function import_request_variables(string $types, $prefix = null): bool {}



































































function error_log(string $message, int $message_type = 0, ?string $destination, ?string $additional_headers): bool {}
