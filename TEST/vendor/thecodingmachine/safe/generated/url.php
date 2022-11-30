<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\UrlException;
function base64_decode(string $string, bool $strict = \false) : string
{
    \error_clear_last();
    $result = \base64_decode($string, $strict);
    if ($result === \false) {
        throw UrlException::createFromPhpError();
    }
    return $result;
}
function get_headers(string $url, bool $associative = \false, $context = null) : array
{
    \error_clear_last();
    if ($context !== null) {
        $result = \get_headers($url, $associative, $context);
    } else {
        $result = \get_headers($url, $associative);
    }
    if ($result === \false) {
        throw UrlException::createFromPhpError();
    }
    return $result;
}
function get_meta_tags(string $filename, bool $use_include_path = \false) : array
{
    \error_clear_last();
    $result = \get_meta_tags($filename, $use_include_path);
    if ($result === \false) {
        throw UrlException::createFromPhpError();
    }
    return $result;
}
function parse_url(string $url, int $component = -1)
{
    \error_clear_last();
    $result = \parse_url($url, $component);
    if ($result === \false) {
        throw UrlException::createFromPhpError();
    }
    return $result;
}
