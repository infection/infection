<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\ZlibException;
function deflate_add($context, string $data, int $flush_mode = \ZLIB_SYNC_FLUSH) : string
{
    \error_clear_last();
    $result = \deflate_add($context, $data, $flush_mode);
    if ($result === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $result;
}
function deflate_init(int $encoding, array $options = [])
{
    \error_clear_last();
    $result = \deflate_init($encoding, $options);
    if ($result === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $result;
}
function gzclose($stream) : void
{
    \error_clear_last();
    $result = \gzclose($stream);
    if ($result === \false) {
        throw ZlibException::createFromPhpError();
    }
}
function gzcompress(string $data, int $level = -1, int $encoding = \ZLIB_ENCODING_DEFLATE) : string
{
    \error_clear_last();
    $result = \gzcompress($data, $level, $encoding);
    if ($result === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $result;
}
function gzdecode(string $data, int $max_length = 0) : string
{
    \error_clear_last();
    $result = \gzdecode($data, $max_length);
    if ($result === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $result;
}
function gzdeflate(string $data, int $level = -1, int $encoding = \ZLIB_ENCODING_RAW) : string
{
    \error_clear_last();
    $result = \gzdeflate($data, $level, $encoding);
    if ($result === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $result;
}
function gzencode(string $data, int $level = -1, int $encoding = \ZLIB_ENCODING_GZIP) : string
{
    \error_clear_last();
    $result = \gzencode($data, $level, $encoding);
    if ($result === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $result;
}
function gzfile(string $filename, int $use_include_path = 0) : array
{
    \error_clear_last();
    $result = \gzfile($filename, $use_include_path);
    if ($result === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $result;
}
function gzgets($stream, int $length = null) : string
{
    \error_clear_last();
    if ($length !== null) {
        $result = \gzgets($stream, $length);
    } else {
        $result = \gzgets($stream);
    }
    if ($result === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $result;
}
function gzgetss($zp, int $length, string $allowable_tags = null) : string
{
    \error_clear_last();
    if ($allowable_tags !== null) {
        $result = \gzgetss($zp, $length, $allowable_tags);
    } else {
        $result = \gzgetss($zp, $length);
    }
    if ($result === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $result;
}
function gzinflate(string $data, int $max_length = 0) : string
{
    \error_clear_last();
    $result = \gzinflate($data, $max_length);
    if ($result === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $result;
}
function gzopen(string $filename, string $mode, int $use_include_path = 0)
{
    \error_clear_last();
    $result = \gzopen($filename, $mode, $use_include_path);
    if ($result === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $result;
}
function gzpassthru($stream) : int
{
    \error_clear_last();
    $result = \gzpassthru($stream);
    if ($result === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $result;
}
function gzread($stream, int $length) : string
{
    \error_clear_last();
    $result = \gzread($stream, $length);
    if ($result === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $result;
}
function gzrewind($stream) : void
{
    \error_clear_last();
    $result = \gzrewind($stream);
    if ($result === \false) {
        throw ZlibException::createFromPhpError();
    }
}
function gzuncompress(string $data, int $max_length = 0) : string
{
    \error_clear_last();
    $result = \gzuncompress($data, $max_length);
    if ($result === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $result;
}
function gzwrite($stream, string $data, int $length = null) : int
{
    \error_clear_last();
    if ($length !== null) {
        $result = \gzwrite($stream, $data, $length);
    } else {
        $result = \gzwrite($stream, $data);
    }
    if ($result === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $result;
}
function inflate_get_read_len($context) : int
{
    \error_clear_last();
    $result = \inflate_get_read_len($context);
    if ($result === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $result;
}
function inflate_get_status($context) : int
{
    \error_clear_last();
    $result = \inflate_get_status($context);
    if ($result === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $result;
}
function inflate_add($context, string $data, int $flush_mode = \ZLIB_SYNC_FLUSH) : string
{
    \error_clear_last();
    $result = \inflate_add($context, $data, $flush_mode);
    if ($result === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $result;
}
function inflate_init(int $encoding, array $options = [])
{
    \error_clear_last();
    $result = \inflate_init($encoding, $options);
    if ($result === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $result;
}
function readgzfile(string $filename, int $use_include_path = 0) : int
{
    \error_clear_last();
    $result = \readgzfile($filename, $use_include_path);
    if ($result === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $result;
}
function zlib_decode(string $data, int $max_length = 0) : string
{
    \error_clear_last();
    $result = \zlib_decode($data, $max_length);
    if ($result === \false) {
        throw ZlibException::createFromPhpError();
    }
    return $result;
}
