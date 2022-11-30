<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\ZlibException;
function deflate_add($context, string $data, int $flush_mode = \ZLIB_SYNC_FLUSH) : string
{
    \error_clear_last();
    $safeResult = \deflate_add($context, $data, $flush_mode);
    if ($safeResult === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $safeResult;
}
function deflate_init(int $encoding, array $options = [])
{
    \error_clear_last();
    $safeResult = \deflate_init($encoding, $options);
    if ($safeResult === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $safeResult;
}
function gzclose($stream) : void
{
    \error_clear_last();
    $safeResult = \gzclose($stream);
    if ($safeResult === \false) {
        throw ZlibException::createFromPhpError();
    }
}
function gzcompress(string $data, int $level = -1, int $encoding = \ZLIB_ENCODING_DEFLATE) : string
{
    \error_clear_last();
    $safeResult = \gzcompress($data, $level, $encoding);
    if ($safeResult === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $safeResult;
}
function gzdecode(string $data, int $max_length = 0) : string
{
    \error_clear_last();
    $safeResult = \gzdecode($data, $max_length);
    if ($safeResult === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $safeResult;
}
function gzdeflate(string $data, int $level = -1, int $encoding = \ZLIB_ENCODING_RAW) : string
{
    \error_clear_last();
    $safeResult = \gzdeflate($data, $level, $encoding);
    if ($safeResult === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $safeResult;
}
function gzencode(string $data, int $level = -1, int $encoding = \ZLIB_ENCODING_GZIP) : string
{
    \error_clear_last();
    $safeResult = \gzencode($data, $level, $encoding);
    if ($safeResult === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $safeResult;
}
function gzfile(string $filename, int $use_include_path = 0) : array
{
    \error_clear_last();
    $safeResult = \gzfile($filename, $use_include_path);
    if ($safeResult === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $safeResult;
}
function gzgets($stream, int $length = null) : string
{
    \error_clear_last();
    if ($length !== null) {
        $safeResult = \gzgets($stream, $length);
    } else {
        $safeResult = \gzgets($stream);
    }
    if ($safeResult === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $safeResult;
}
function gzgetss($zp, int $length, string $allowable_tags = null) : string
{
    \error_clear_last();
    if ($allowable_tags !== null) {
        $safeResult = \gzgetss($zp, $length, $allowable_tags);
    } else {
        $safeResult = \gzgetss($zp, $length);
    }
    if ($safeResult === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $safeResult;
}
function gzinflate(string $data, int $max_length = 0) : string
{
    \error_clear_last();
    $safeResult = \gzinflate($data, $max_length);
    if ($safeResult === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $safeResult;
}
function gzopen(string $filename, string $mode, int $use_include_path = 0)
{
    \error_clear_last();
    $safeResult = \gzopen($filename, $mode, $use_include_path);
    if ($safeResult === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $safeResult;
}
function gzpassthru($stream) : int
{
    \error_clear_last();
    $safeResult = \gzpassthru($stream);
    if ($safeResult === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $safeResult;
}
function gzread($stream, int $length) : string
{
    \error_clear_last();
    $safeResult = \gzread($stream, $length);
    if ($safeResult === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $safeResult;
}
function gzrewind($stream) : void
{
    \error_clear_last();
    $safeResult = \gzrewind($stream);
    if ($safeResult === \false) {
        throw ZlibException::createFromPhpError();
    }
}
function gzuncompress(string $data, int $max_length = 0) : string
{
    \error_clear_last();
    $safeResult = \gzuncompress($data, $max_length);
    if ($safeResult === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $safeResult;
}
function gzwrite($stream, string $data, int $length = null) : int
{
    \error_clear_last();
    if ($length !== null) {
        $safeResult = \gzwrite($stream, $data, $length);
    } else {
        $safeResult = \gzwrite($stream, $data);
    }
    if ($safeResult === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $safeResult;
}
function inflate_get_read_len($context) : int
{
    \error_clear_last();
    $safeResult = \inflate_get_read_len($context);
    if ($safeResult === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $safeResult;
}
function inflate_get_status($context) : int
{
    \error_clear_last();
    $safeResult = \inflate_get_status($context);
    if ($safeResult === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $safeResult;
}
function inflate_add($context, string $data, int $flush_mode = \ZLIB_SYNC_FLUSH) : string
{
    \error_clear_last();
    $safeResult = \inflate_add($context, $data, $flush_mode);
    if ($safeResult === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $safeResult;
}
function inflate_init(int $encoding, array $options = [])
{
    \error_clear_last();
    $safeResult = \inflate_init($encoding, $options);
    if ($safeResult === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $safeResult;
}
function readgzfile(string $filename, int $use_include_path = 0) : int
{
    \error_clear_last();
    $safeResult = \readgzfile($filename, $use_include_path);
    if ($safeResult === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $safeResult;
}
function zlib_decode(string $data, int $max_length = 0) : string
{
    \error_clear_last();
    $safeResult = \zlib_decode($data, $max_length);
    if ($safeResult === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $safeResult;
}
