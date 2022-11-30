<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\MbstringException;
function mb_chr(int $codepoint, string $encoding = null) : string
{
    \error_clear_last();
    if ($encoding !== null) {
        $result = \mb_chr($codepoint, $encoding);
    } else {
        $result = \mb_chr($codepoint);
    }
    if ($result === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $result;
}
function mb_convert_encoding($string, string $to_encoding, $from_encoding = null)
{
    \error_clear_last();
    if ($from_encoding !== null) {
        $result = \mb_convert_encoding($string, $to_encoding, $from_encoding);
    } else {
        $result = \mb_convert_encoding($string, $to_encoding);
    }
    if ($result === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $result;
}
function mb_detect_order($encoding = null)
{
    \error_clear_last();
    if ($encoding !== null) {
        $result = \mb_detect_order($encoding);
    } else {
        $result = \mb_detect_order();
    }
    if ($result === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $result;
}
function mb_encoding_aliases(string $encoding) : array
{
    \error_clear_last();
    $result = \mb_encoding_aliases($encoding);
    if ($result === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $result;
}
function mb_ereg_replace_callback(string $pattern, callable $callback, string $string, string $options = null) : string
{
    \error_clear_last();
    if ($options !== null) {
        $result = \mb_ereg_replace_callback($pattern, $callback, $string, $options);
    } else {
        $result = \mb_ereg_replace_callback($pattern, $callback, $string);
    }
    if ($result === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $result;
}
function mb_ereg_replace(string $pattern, string $replacement, string $string, string $options = null) : string
{
    \error_clear_last();
    if ($options !== null) {
        $result = \mb_ereg_replace($pattern, $replacement, $string, $options);
    } else {
        $result = \mb_ereg_replace($pattern, $replacement, $string);
    }
    if ($result === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $result;
}
function mb_ereg_search_getregs() : array
{
    \error_clear_last();
    $result = \mb_ereg_search_getregs();
    if ($result === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $result;
}
function mb_ereg_search_init(string $string, string $pattern = null, string $options = null) : void
{
    \error_clear_last();
    if ($options !== null) {
        $result = \mb_ereg_search_init($string, $pattern, $options);
    } elseif ($pattern !== null) {
        $result = \mb_ereg_search_init($string, $pattern);
    } else {
        $result = \mb_ereg_search_init($string);
    }
    if ($result === \false) {
        throw MbstringException::createFromPhpError();
    }
}
function mb_ereg_search_regs(string $pattern = null, string $options = null) : array
{
    \error_clear_last();
    if ($options !== null) {
        $result = \mb_ereg_search_regs($pattern, $options);
    } elseif ($pattern !== null) {
        $result = \mb_ereg_search_regs($pattern);
    } else {
        $result = \mb_ereg_search_regs();
    }
    if ($result === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $result;
}
function mb_ereg_search_setpos(int $offset) : void
{
    \error_clear_last();
    $result = \mb_ereg_search_setpos($offset);
    if ($result === \false) {
        throw MbstringException::createFromPhpError();
    }
}
function mb_eregi_replace(string $pattern, string $replacement, string $string, string $options = null) : string
{
    \error_clear_last();
    if ($options !== null) {
        $result = \mb_eregi_replace($pattern, $replacement, $string, $options);
    } else {
        $result = \mb_eregi_replace($pattern, $replacement, $string);
    }
    if ($result === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $result;
}
function mb_get_info(string $type = "all")
{
    \error_clear_last();
    $result = \mb_get_info($type);
    if ($result === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $result;
}
function mb_http_output(string $encoding = null)
{
    \error_clear_last();
    if ($encoding !== null) {
        $result = \mb_http_output($encoding);
    } else {
        $result = \mb_http_output();
    }
    if ($result === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $result;
}
function mb_internal_encoding(string $encoding = null)
{
    \error_clear_last();
    if ($encoding !== null) {
        $result = \mb_internal_encoding($encoding);
    } else {
        $result = \mb_internal_encoding();
    }
    if ($result === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $result;
}
function mb_ord(string $string, string $encoding = null) : int
{
    \error_clear_last();
    if ($encoding !== null) {
        $result = \mb_ord($string, $encoding);
    } else {
        $result = \mb_ord($string);
    }
    if ($result === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $result;
}
function mb_parse_str(string $string, ?array &$result) : void
{
    \error_clear_last();
    $result = \mb_parse_str($string, $result);
    if ($result === \false) {
        throw MbstringException::createFromPhpError();
    }
}
function mb_regex_encoding(string $encoding = null)
{
    \error_clear_last();
    if ($encoding !== null) {
        $result = \mb_regex_encoding($encoding);
    } else {
        $result = \mb_regex_encoding();
    }
    if ($result === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $result;
}
function mb_send_mail(string $to, string $subject, string $message, $additional_headers = [], string $additional_params = null) : void
{
    \error_clear_last();
    if ($additional_params !== null) {
        $result = \mb_send_mail($to, $subject, $message, $additional_headers, $additional_params);
    } else {
        $result = \mb_send_mail($to, $subject, $message, $additional_headers);
    }
    if ($result === \false) {
        throw MbstringException::createFromPhpError();
    }
}
function mb_split(string $pattern, string $string, int $limit = -1) : array
{
    \error_clear_last();
    $result = \mb_split($pattern, $string, $limit);
    if ($result === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $result;
}
