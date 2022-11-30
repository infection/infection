<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\MiscException;
function define(string $constant_name, $value, bool $case_insensitive = \false) : void
{
    \error_clear_last();
    $safeResult = \define($constant_name, $value, $case_insensitive);
    if ($safeResult === \false) {
        throw MiscException::createFromPhpError();
    }
}
function highlight_file(string $filename, bool $return = \false)
{
    \error_clear_last();
    $safeResult = \highlight_file($filename, $return);
    if ($safeResult === \false) {
        throw MiscException::createFromPhpError();
    }
    return $safeResult;
}
function highlight_string(string $string, bool $return = \false)
{
    \error_clear_last();
    $safeResult = \highlight_string($string, $return);
    if ($safeResult === \false) {
        throw MiscException::createFromPhpError();
    }
    return $safeResult;
}
function hrtime(bool $as_number = \false)
{
    \error_clear_last();
    $safeResult = \hrtime($as_number);
    if ($safeResult === \false) {
        throw MiscException::createFromPhpError();
    }
    return $safeResult;
}
/**
 * Pack given arguments into a binary string according to
 * format.
 *
 * The idea for this function was taken from Perl and all formatting codes
 * work the same as in Perl. However, there are some formatting codes that are
 * missing such as Perl's "u" format code.
 *
 * Note that the distinction between signed and unsigned values only
 * affects the function unpack, where as
 * function pack gives the same result for
 * signed and unsigned format codes.
 *
 * @param string $format The format string consists of format codes
 * followed by an optional repeater argument. The repeater argument can
 * be either an integer value or * for repeating to
 * the end of the input data. For a, A, h, H the repeat count specifies
 * how many characters of one data argument are taken, for @ it is the
 * absolute position where to put the next data, for everything else the
 * repeat count specifies how many data arguments are consumed and packed
 * into the resulting binary string.
 *
 * Currently implemented formats are:
 *
 * pack format characters
 *
 *
 *
 * Code
 * Description
 *
 *
 *
 *
 * a
 * NUL-padded string
 *
 *
 * A
 * SPACE-padded string
 *
 * h
 * Hex string, low nibble first
 *
 * H
 * Hex string, high nibble first
 * csigned char
 *
 * C
 * unsigned char
 *
 * s
 * signed short (always 16 bit, machine byte order)
 *
 *
 * S
 * unsigned short (always 16 bit, machine byte order)
 *
 *
 * n
 * unsigned short (always 16 bit, big endian byte order)
 *
 *
 * v
 * unsigned short (always 16 bit, little endian byte order)
 *
 *
 * i
 * signed integer (machine dependent size and byte order)
 *
 *
 * I
 * unsigned integer (machine dependent size and byte order)
 *
 *
 * l
 * signed long (always 32 bit, machine byte order)
 *
 *
 * L
 * unsigned long (always 32 bit, machine byte order)
 *
 *
 * N
 * unsigned long (always 32 bit, big endian byte order)
 *
 *
 * V
 * unsigned long (always 32 bit, little endian byte order)
 *
 *
 * q
 * signed long long (always 64 bit, machine byte order)
 *
 *
 * Q
 * unsigned long long (always 64 bit, machine byte order)
 *
 *
 * J
 * unsigned long long (always 64 bit, big endian byte order)
 *
 *
 * P
 * unsigned long long (always 64 bit, little endian byte order)
 *
 *
 * f
 * float (machine dependent size and representation)
 *
 *
 * g
 * float (machine dependent size, little endian byte order)
 *
 *
 * G
 * float (machine dependent size, big endian byte order)
 *
 *
 * d
 * double (machine dependent size and representation)
 *
 *
 * e
 * double (machine dependent size, little endian byte order)
 *
 *
 * E
 * double (machine dependent size, big endian byte order)
 *
 *
 * x
 * NUL byte
 *
 *
 * X
 * Back up one byte
 *
 *
 * Z
 * NUL-padded string
 *
 *
 * @
 * NUL-fill to absolute position
 *
 *
 *
 *
 * @param mixed $values
 * @return string Returns a binary string containing data.
 * @throws MiscException
 *
 */
function pack(string $format, ...$values) : string
{
    \error_clear_last();
    if ($values !== []) {
        $safeResult = \pack($format, ...$values);
    } else {
        $safeResult = \pack($format);
    }
    if ($safeResult === \false) {
        throw MiscException::createFromPhpError();
    }
    return $safeResult;
}
function sapi_windows_cp_conv($in_codepage, $out_codepage, string $subject) : string
{
    \error_clear_last();
    $safeResult = \sapi_windows_cp_conv($in_codepage, $out_codepage, $subject);
    if ($safeResult === null) {
        throw MiscException::createFromPhpError();
    }
    return $safeResult;
}
function sapi_windows_cp_set(int $codepage) : void
{
    \error_clear_last();
    $safeResult = \sapi_windows_cp_set($codepage);
    if ($safeResult === \false) {
        throw MiscException::createFromPhpError();
    }
}
function sapi_windows_generate_ctrl_event(int $event, int $pid = 0) : void
{
    \error_clear_last();
    $safeResult = \sapi_windows_generate_ctrl_event($event, $pid);
    if ($safeResult === \false) {
        throw MiscException::createFromPhpError();
    }
}
function sapi_windows_set_ctrl_handler($handler, bool $add = \true) : void
{
    \error_clear_last();
    $safeResult = \sapi_windows_set_ctrl_handler($handler, $add);
    if ($safeResult === \false) {
        throw MiscException::createFromPhpError();
    }
}
function sapi_windows_vt100_support($stream, bool $enable = null) : void
{
    \error_clear_last();
    if ($enable !== null) {
        $safeResult = \sapi_windows_vt100_support($stream, $enable);
    } else {
        $safeResult = \sapi_windows_vt100_support($stream);
    }
    if ($safeResult === \false) {
        throw MiscException::createFromPhpError();
    }
}
function time_nanosleep(int $seconds, int $nanoseconds)
{
    \error_clear_last();
    $safeResult = \time_nanosleep($seconds, $nanoseconds);
    if ($safeResult === \false) {
        throw MiscException::createFromPhpError();
    }
    return $safeResult;
}
function time_sleep_until(float $timestamp) : void
{
    \error_clear_last();
    $safeResult = \time_sleep_until($timestamp);
    if ($safeResult === \false) {
        throw MiscException::createFromPhpError();
    }
}
function unpack(string $format, string $string, int $offset = 0) : array
{
    \error_clear_last();
    $safeResult = \unpack($format, $string, $offset);
    if ($safeResult === \false) {
        throw MiscException::createFromPhpError();
    }
    return $safeResult;
}
