<?php


use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Pure;














function readgzfile(string $filename, int $use_include_path = 0): int|false {}










function gzrewind($stream): bool {}










function gzclose($stream): bool {}











function gzeof($stream): bool {}










function gzgetc($stream): string|false {}













function gzgets($stream, ?int $length = null): string|false {}

/**
@removed














*/
#[Deprecated(since: "7.3")]
function gzgetss($zp, int $length, $allowable_tags) {}













function gzread($stream, int $length): string|false {}




























function gzopen(string $filename, string $mode, int $use_include_path = 0) {}











function gzpassthru($stream): int {}























function gzseek($stream, int $offset, int $whence = SEEK_SET): int {}










function gztell($stream): int|false {}


























function gzwrite($stream, string $data, ?int $length): int|false {}









function gzputs($stream, string $data, ?int $length): int|false {}













function gzfile(string $filename, int $use_include_path = 0): array|false {}



















#[Pure]
function gzcompress(string $data, int $level = -1, int $encoding = ZLIB_ENCODING_DEFLATE): string|false {}

















#[Pure]
function gzuncompress(string $data, int $max_length = 0): string|false {}

















#[Pure]
function gzdeflate(string $data, int $level = -1, int $encoding = ZLIB_ENCODING_RAW): string|false {}

















#[Pure]
function gzinflate(string $data, int $max_length = 0): string|false {}




























#[Pure]
function gzencode(string $data, int $level = -1, int $encoding = FORCE_GZIP): string|false {}













#[Pure]
function gzdecode(string $data, int $max_length = 0): string|false {}













#[Pure]
function zlib_encode(string $data, int $encoding, int $level = -1): string|false {}











#[Pure]
function zlib_decode(string $data, int $max_length = 0): string|false {}







#[Pure]
function zlib_get_coding_type(): string|false {}








function ob_gzhandler(string $data, int $flags): string|false {}






















#[Pure]
#[LanguageLevelTypeAware(["8.0" => "DeflateContext|false"], default: "resource|false")]
function deflate_init(int $encoding, array $options = []) {}






















function deflate_add(#[LanguageLevelTypeAware(["8.0" => "DeflateContext"], default: "resource")] $context, string $data, int $flush_mode = ZLIB_SYNC_FLUSH): string|false {}






















#[Pure]
#[LanguageLevelTypeAware(["8.0" => "InflateContext|false"], default: "resource|false")]
function inflate_init(int $encoding, array $options = []) {}






















function inflate_add(#[LanguageLevelTypeAware(["8.0" => "InflateContext"], default: "resource")] $context, string $data, int $flush_mode = ZLIB_SYNC_FLUSH): string|false {}







#[Pure]
function inflate_get_read_len(#[LanguageLevelTypeAware(["8.0" => "InflateContext"], default: "resource")] $context): int {}







#[Pure]
function inflate_get_status(#[LanguageLevelTypeAware(["8.0" => "InflateContext"], default: "resource")] $context): int {}




final class InflateContext
{




private function __construct() {}
}




final class DeflateContext
{




private function __construct() {}
}

define('FORCE_GZIP', 31);
define('FORCE_DEFLATE', 15);

define('ZLIB_ENCODING_RAW', -15);

define('ZLIB_ENCODING_GZIP', 31);

define('ZLIB_ENCODING_DEFLATE', 15);

define('ZLIB_NO_FLUSH', 0);
define('ZLIB_PARTIAL_FLUSH', 1);
define('ZLIB_SYNC_FLUSH', 2);
define('ZLIB_FULL_FLUSH', 3);
define('ZLIB_BLOCK', 5);
define('ZLIB_FINISH', 4);

define('ZLIB_FILTERED', 1);
define('ZLIB_HUFFMAN_ONLY', 2);
define('ZLIB_RLE', 3);
define('ZLIB_FIXED', 4);
define('ZLIB_DEFAULT_STRATEGY', 0);
define('ZLIB_OK', 0);
define('ZLIB_STREAM_END', 1);
define('ZLIB_NEED_DICT', 2);
define('ZLIB_ERRNO', -1);
define('ZLIB_STREAM_ERROR', -2);
define('ZLIB_DATA_ERROR', -3);
define('ZLIB_MEM_ERROR', -4);
define('ZLIB_BUF_ERROR', -5);
define('ZLIB_VERSION_ERROR', -6);

define('ZLIB_VERSION', 'zlib_version_string'); 
define('ZLIB_VERNUM', 'zlib_version_string'); 
