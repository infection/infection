<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\UrlException;
function base64_decode(string $string, bool $strict = \false) : string
{
    \error_clear_last();
    $safeResult = \base64_decode($string, $strict);
    if ($safeResult === \false) {
        throw UrlException::createFromPhpError();
    }
    return $safeResult;
}
function get_headers(string $url, bool $associative = \false, $context = null) : array
{
    \error_clear_last();
    if ($context !== null) {
        $safeResult = \get_headers($url, $associative, $context);
    } else {
        $safeResult = \get_headers($url, $associative);
    }
    if ($safeResult === \false) {
        throw UrlException::createFromPhpError();
    }
    return $safeResult;
}
function get_meta_tags(string $filename, bool $use_include_path = \false) : array
{
    \error_clear_last();
    $safeResult = \get_meta_tags($filename, $use_include_path);
    if ($safeResult === \false) {
        throw UrlException::createFromPhpError();
    }
    return $safeResult;
}
function parse_url(string $url, int $component = -1)
{
    \error_clear_last();
    $safeResult = \parse_url($url, $component);
    if ($safeResult === \false) {
        throw UrlException::createFromPhpError();
    }
    return $safeResult;
}
