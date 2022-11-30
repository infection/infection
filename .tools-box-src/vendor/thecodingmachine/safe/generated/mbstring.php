<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\MbstringException;
function mb_chr(int $codepoint, string $encoding = null) : string
{
    \error_clear_last();
    if ($encoding !== null) {
        $safeResult = \mb_chr($codepoint, $encoding);
    } else {
        $safeResult = \mb_chr($codepoint);
    }
    if ($safeResult === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $safeResult;
}
function mb_convert_encoding($string, string $to_encoding, $from_encoding = null)
{
    \error_clear_last();
    if ($from_encoding !== null) {
        $safeResult = \mb_convert_encoding($string, $to_encoding, $from_encoding);
    } else {
        $safeResult = \mb_convert_encoding($string, $to_encoding);
    }
    if ($safeResult === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $safeResult;
}
function mb_detect_order($encoding = null)
{
    \error_clear_last();
    if ($encoding !== null) {
        $safeResult = \mb_detect_order($encoding);
    } else {
        $safeResult = \mb_detect_order();
    }
    if ($safeResult === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $safeResult;
}
function mb_encoding_aliases(string $encoding) : array
{
    \error_clear_last();
    $safeResult = \mb_encoding_aliases($encoding);
    if ($safeResult === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $safeResult;
}
function mb_ereg_replace_callback(string $pattern, callable $callback, string $string, string $options = null) : ?string
{
    \error_clear_last();
    if ($options !== null) {
        $safeResult = \mb_ereg_replace_callback($pattern, $callback, $string, $options);
    } else {
        $safeResult = \mb_ereg_replace_callback($pattern, $callback, $string);
    }
    if ($safeResult === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $safeResult;
}
function mb_ereg_replace(string $pattern, string $replacement, string $string, string $options = null) : ?string
{
    \error_clear_last();
    if ($options !== null) {
        $safeResult = \mb_ereg_replace($pattern, $replacement, $string, $options);
    } else {
        $safeResult = \mb_ereg_replace($pattern, $replacement, $string);
    }
    if ($safeResult === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $safeResult;
}
function mb_ereg_search_getregs() : array
{
    \error_clear_last();
    $safeResult = \mb_ereg_search_getregs();
    if ($safeResult === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $safeResult;
}
function mb_ereg_search_init(string $string, string $pattern = null, string $options = null) : void
{
    \error_clear_last();
    if ($options !== null) {
        $safeResult = \mb_ereg_search_init($string, $pattern, $options);
    } elseif ($pattern !== null) {
        $safeResult = \mb_ereg_search_init($string, $pattern);
    } else {
        $safeResult = \mb_ereg_search_init($string);
    }
    if ($safeResult === \false) {
        throw MbstringException::createFromPhpError();
    }
}
function mb_ereg_search_regs(string $pattern = null, string $options = null) : array
{
    \error_clear_last();
    if ($options !== null) {
        $safeResult = \mb_ereg_search_regs($pattern, $options);
    } elseif ($pattern !== null) {
        $safeResult = \mb_ereg_search_regs($pattern);
    } else {
        $safeResult = \mb_ereg_search_regs();
    }
    if ($safeResult === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $safeResult;
}
function mb_ereg_search_setpos(int $offset) : void
{
    \error_clear_last();
    $safeResult = \mb_ereg_search_setpos($offset);
    if ($safeResult === \false) {
        throw MbstringException::createFromPhpError();
    }
}
function mb_eregi_replace(string $pattern, string $replacement, string $string, string $options = null) : string
{
    \error_clear_last();
    if ($options !== null) {
        $safeResult = \mb_eregi_replace($pattern, $replacement, $string, $options);
    } else {
        $safeResult = \mb_eregi_replace($pattern, $replacement, $string);
    }
    if ($safeResult === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $safeResult;
}
function mb_get_info(string $type = "all")
{
    \error_clear_last();
    $safeResult = \mb_get_info($type);
    if ($safeResult === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $safeResult;
}
function mb_http_output(string $encoding = null)
{
    \error_clear_last();
    if ($encoding !== null) {
        $safeResult = \mb_http_output($encoding);
    } else {
        $safeResult = \mb_http_output();
    }
    if ($safeResult === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $safeResult;
}
function mb_internal_encoding(string $encoding = null)
{
    \error_clear_last();
    if ($encoding !== null) {
        $safeResult = \mb_internal_encoding($encoding);
    } else {
        $safeResult = \mb_internal_encoding();
    }
    if ($safeResult === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $safeResult;
}
function mb_ord(string $string, string $encoding = null) : int
{
    \error_clear_last();
    if ($encoding !== null) {
        $safeResult = \mb_ord($string, $encoding);
    } else {
        $safeResult = \mb_ord($string);
    }
    if ($safeResult === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $safeResult;
}
function mb_parse_str(string $string, ?array &$result) : void
{
    \error_clear_last();
    $safeResult = \mb_parse_str($string, $result);
    if ($safeResult === \false) {
        throw MbstringException::createFromPhpError();
    }
}
function mb_regex_encoding(string $encoding = null)
{
    \error_clear_last();
    if ($encoding !== null) {
        $safeResult = \mb_regex_encoding($encoding);
    } else {
        $safeResult = \mb_regex_encoding();
    }
    if ($safeResult === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $safeResult;
}
function mb_send_mail(string $to, string $subject, string $message, $additional_headers = [], string $additional_params = null) : void
{
    \error_clear_last();
    if ($additional_params !== null) {
        $safeResult = \mb_send_mail($to, $subject, $message, $additional_headers, $additional_params);
    } else {
        $safeResult = \mb_send_mail($to, $subject, $message, $additional_headers);
    }
    if ($safeResult === \false) {
        throw MbstringException::createFromPhpError();
    }
}
function mb_split(string $pattern, string $string, int $limit = -1) : array
{
    \error_clear_last();
    $safeResult = \mb_split($pattern, $string, $limit);
    if ($safeResult === \false) {
        throw MbstringException::createFromPhpError();
    }
    return $safeResult;
}
