<?php



use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Pure;

















#[Pure]
function mb_convert_case(string $string, int $mode, ?string $encoding): string {}










#[Pure]
function mb_strtoupper(string $string, ?string $encoding): string {}










#[Pure]
function mb_strtolower(string $string, ?string $encoding): string {}























function mb_language(?string $language): string|bool {}















function mb_internal_encoding(?string $encoding): string|bool {}














#[Pure]
function mb_http_input(?string $type): array|string|false {}



















function mb_http_output(?string $encoding): string|bool {}












































function mb_detect_order(array|string|null $encoding = null): array|bool {}
















function mb_substitute_character(string|int|null $substitute_character = null): string|int|bool {}












#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')]
function mb_parse_str(string $string, &$result): bool {}












#[PhpStormStubsElementAvailable(from: '8.0')]
function mb_parse_str(string $string, &$result): bool {}












#[Pure]
function mb_output_handler(string $string, int $status): string {}










#[Pure]
function mb_preferred_mime_name(string $encoding): string|false {}













#[Pure]
#[LanguageLevelTypeAware(['8.0' => 'int'], default: 'int|false')]
function mb_strlen(string $string, #[LanguageLevelTypeAware(['8.0' => 'string|null'], default: 'string')] $encoding) {}



















#[Pure]
function mb_strpos(string $haystack, string $needle, int $offset = 0, ?string $encoding): int|false {}




















#[Pure]
function mb_strrpos(string $haystack, string $needle, int $offset = 0, ?string $encoding): int|false {}























#[Pure]
function mb_stripos(string $haystack, string $needle, int $offset = 0, ?string $encoding): int|false {}
























#[Pure]
function mb_strripos(string $haystack, string $needle, int $offset = 0, ?string $encoding): int|false {}


























#[Pure]
function mb_strstr(string $haystack, string $needle, bool $before_needle = false, ?string $encoding): string|false {}


























#[Pure]
function mb_strrchr(string $haystack, string $needle, bool $before_needle = false, ?string $encoding): string|false {}


























#[Pure]
function mb_stristr(string $haystack, string $needle, bool $before_needle = false, ?string $encoding): string|false {}


























#[Pure]
function mb_strrichr(string $haystack, string $needle, bool $before_needle = false, ?string $encoding): string|false {}















#[Pure]
function mb_substr_count(string $haystack, string $needle, ?string $encoding): int {}



















#[Pure]
function mb_substr(string $string, int $start, ?int $length, ?string $encoding): string {}



















#[Pure]
function mb_strcut(string $string, int $start, ?int $length, ?string $encoding): string {}










#[Pure]
function mb_strwidth(string $string, ?string $encoding): int {}






















#[Pure]
function mb_strimwidth(string $string, int $start, int $width, string $trim_marker = '', ?string $encoding): string {}






















#[Pure]
function mb_convert_encoding(array|string $string, string $to_encoding, array|string|null $from_encoding = null): array|string|false {}
























#[Pure]
function mb_detect_encoding(string $string, array|string|null $encodings = null, bool $strict = false): string|false {}






#[Pure]
function mb_list_encodings(): array {}







#[Pure]
#[LanguageLevelTypeAware(["8.0" => "array"], default: "array|false")]
function mb_encoding_aliases(string $encoding) {}



















































































































#[Pure]
function mb_convert_kana(string $string, string $mode = 'KV', ?string $encoding): string {}

































#[Pure]
function mb_encode_mimeheader(string $string, ?string $charset, ?string $transfer_encoding, string $newline = "\n", int $indent = 0): string {}









#[Pure]
function mb_decode_mimeheader(string $string): string {}























function mb_convert_variables(
string $to_encoding,
array|string $from_encoding,
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] &$vars,
#[PhpStormStubsElementAvailable(from: '8.0')] mixed &$var,
mixed &...$vars
): string|false {}















#[Pure]
function mb_encode_numericentity(string $string, array $map, ?string $encoding = null, bool $hex = false): string {}

















#[Pure]
#[LanguageLevelTypeAware(['8.0' => 'string'], default: 'string|false|null')]
function mb_decode_numericentity(string $string, array $map, ?string $encoding = null, #[PhpStormStubsElementAvailable(from: '7.2', to: '7.4')] $is_hex = false) {}






























function mb_send_mail(string $to, string $subject, string $message, array|string $additional_headers = [], ?string $additional_params): bool {}


















#[Pure]
function mb_get_info(string $type = 'all'): array|string|int|false {}














#[Pure]
function mb_check_encoding(array|string|null $value = null, ?string $encoding): bool {}










function mb_regex_encoding(?string $encoding): string|bool {}










function mb_regex_set_options(?string $options): string {}















function mb_ereg(string $pattern, string $string, &$matches): bool {}















#[LanguageLevelTypeAware(["8.0" => "bool"], default: "false|int")]
function mb_eregi(string $pattern, string $string, &$matches): bool {}





























#[Pure]
function mb_ereg_replace(string $pattern, string $replacement, string $string, ?string $options = null): string|false|null {}












































function mb_ereg_replace_callback(string $pattern, callable $callback, string $string, ?string $options = null): string|false|null {}


















#[Pure]
function mb_eregi_replace(
string $pattern,
string $replacement,
string $string,
#[PhpStormStubsElementAvailable(from: '7.0')] ?string $options = null
): string|false|null {}















#[Pure]
function mb_split(string $pattern, string $string, int $limit = -1): array|false {}














#[Pure]
function mb_ereg_match(string $pattern, string $string, ?string $options): bool {}












#[Pure]
function mb_ereg_search(?string $pattern, ?string $options): bool {}















#[Pure]
function mb_ereg_search_pos(?string $pattern, ?string $options): array|false {}
















#[Pure]
function mb_ereg_search_regs(?string $pattern, ?string $options): array|false {}















function mb_ereg_search_init(string $string, ?string $pattern, ?string $options): bool {}











#[Pure]
function mb_ereg_search_getregs(): array|false {}






#[Pure]
#[Deprecated(since: '7.3')]
function mb_ereg_search_getpos(): int {}









#[Pure]
function mb_ereg_search_setpos(int $offset): bool {}

/**
@removed


*/
#[Deprecated(replacement: "mb_regex_encoding(%parametersList%)", since: "7.3")]
function mbregex_encoding($encoding) {}

/**
@removed




*/
#[Deprecated(replacement: 'mb_ereg(%parametersList%)', since: '7.3')]
function mbereg(string $pattern, string $string, array &$registers) {}

/**
@removed




*/
#[Deprecated(replacement: "mb_eregi(%parametersList%)", since: "7.3")]
function mberegi(string $pattern, string $string, array &$registers) {}

/**
@removed





*/
#[Deprecated(replacement: 'mb_ereg_replace(%parametersList%)', since: '7.3')]
function mbereg_replace($pattern, $replacement, $string, $option) {}

/**
@removed






*/
#[Deprecated(replacement: "mb_eregi_replace(%parametersList%)", since: "7.3")]
function mberegi_replace(
$pattern,
$replacement,
$string,
#[PhpStormStubsElementAvailable(from: '7.0')] string $option = "msri"
): string {}

/**
@removed




*/
#[Deprecated(replacement: 'mb_split(%parametersList%)', since: '7.3')]
function mbsplit($pattern, $string, $limit) {}

/**
@removed




*/
#[Deprecated(replacement: "mb_ereg_match(%parametersList%)", since: "7.3")]
function mbereg_match($pattern, $string, $option) {}

/**
@removed



*/
#[Deprecated("use mb_ereg_search instead", replacement: "mb_ereg_search(%parametersList%)", since: "7.3")]
function mbereg_search($pattern, $option) {}

/**
@removed



*/
#[Deprecated(replacement: "mb_ereg_search_pos(%parametersList%)", since: "7.3")]
function mbereg_search_pos($pattern, $option) {}

/**
@removed



*/
#[Deprecated(replacement: 'mb_ereg_search_regs(%parametersList%)', since: '7.3')]
function mbereg_search_regs($pattern, $option) {}

/**
@removed




*/
#[Deprecated(replacement: "mb_ereg_search_init(%parametersList%)", since: "7.3")]
function mbereg_search_init($string, $pattern, $option) {}

/**
@removed

*/
#[Deprecated(replacement: 'mb_ereg_search_getregs(%parametersList%)', since: '7.3')]
function mbereg_search_getregs() {}

/**
@removed

*/
#[Deprecated(replacement: "mb_ereg_search_getpos()", since: "7.3")]
function mbereg_search_getpos() {}









#[Pure]
function mb_chr(int $codepoint, ?string $encoding): string|false {}









#[Pure]
function mb_ord(string $string, ?string $encoding): int|false {}









#[Pure]
#[LanguageLevelTypeAware(["8.0" => "string"], default: "string|false")]
function mb_scrub(string $string, ?string $encoding): false|string {}





#[Deprecated(replacement: "mb_ereg_search_setpos(%parametersList%)", since: "7.3")]
#[Pure]
function mbereg_search_setpos($position) {}
















#[Pure]
#[LanguageLevelTypeAware(["8.0" => "array"], default: "array|false")]
function mb_str_split(string $string, int $length = 1, ?string $encoding) {}

/**
@removed
*/
define('MB_OVERLOAD_MAIL', 1);
/**
@removed
*/
define('MB_OVERLOAD_STRING', 2);
/**
@removed
*/
define('MB_OVERLOAD_REGEX', 4);
define('MB_CASE_UPPER', 0);
define('MB_CASE_LOWER', 1);
define('MB_CASE_TITLE', 2);



define('MB_CASE_FOLD', 3);



define('MB_CASE_UPPER_SIMPLE', 4);



define('MB_CASE_LOWER_SIMPLE', 5);



define('MB_CASE_TITLE_SIMPLE', 6);



define('MB_CASE_FOLD_SIMPLE', 7);




define('MB_ONIGURUMA_VERSION', '6.9.8');


