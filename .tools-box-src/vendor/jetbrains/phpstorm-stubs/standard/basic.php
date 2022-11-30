<?php

use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Pure;



































#[Deprecated(since: '5.3')]
function dl(string $extension_filename): bool {}










function cli_set_process_title(string $title): bool {}








#[Pure(true)]
function cli_get_process_title(): ?string {}








#[Pure]
function is_iterable(mixed $value): bool {}










#[Pure]
#[Deprecated(replacement: "mb_convert_encoding", since: "8.2")]
function utf8_encode(string $string): string {}











#[Pure]
#[Deprecated(replacement: "mb_convert_encoding", since: "8.2")]
function utf8_decode(string $string): string {}







function error_clear_last(): void {}












function sapi_windows_cp_get(string $kind = ""): int {}








function sapi_windows_cp_set(int $codepage): bool {}










function sapi_windows_cp_conv(int|string $in_codepage, int|string $out_codepage, string $subject): ?string {}







function sapi_windows_cp_is_utf8(): bool {}
































function sapi_windows_vt100_support($stream, ?bool $enable = null): bool {}























function sapi_windows_set_ctrl_handler(?callable $handler, bool $add = true): bool {}










function sapi_windows_generate_ctrl_event(int $event, int $pid = 0): bool {}









define('__FILE__', '', true);





define('__LINE__', 0, true);











define('__CLASS__', '', true);







define('__FUNCTION__', '', true);






define('__METHOD__', '', true);








define('__TRAIT__', '', true);








define('__DIR__', '', true);






define('__NAMESPACE__', '', true);
