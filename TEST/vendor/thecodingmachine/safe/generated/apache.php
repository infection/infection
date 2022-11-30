<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\ApacheException;
function apache_get_version() : string
{
    \error_clear_last();
    $result = \apache_get_version();
    if ($result === \false) {
        throw ApacheException::createFromPhpError();
    }
    return $result;
}
function apache_getenv(string $variable, bool $walk_to_top = \false) : string
{
    \error_clear_last();
    $result = \apache_getenv($variable, $walk_to_top);
    if ($result === \false) {
        throw ApacheException::createFromPhpError();
    }
    return $result;
}
function apache_lookup_uri(string $filename) : object
{
    \error_clear_last();
    $result = \apache_lookup_uri($filename);
    if ($result === \false) {
        throw ApacheException::createFromPhpError();
    }
    return $result;
}
function apache_request_headers() : array
{
    \error_clear_last();
    $result = \apache_request_headers();
    if ($result === \false) {
        throw ApacheException::createFromPhpError();
    }
    return $result;
}
function apache_response_headers() : array
{
    \error_clear_last();
    $result = \apache_response_headers();
    if ($result === \false) {
        throw ApacheException::createFromPhpError();
    }
    return $result;
}
function apache_setenv(string $variable, string $value, bool $walk_to_top = \false) : void
{
    \error_clear_last();
    $result = \apache_setenv($variable, $value, $walk_to_top);
    if ($result === \false) {
        throw ApacheException::createFromPhpError();
    }
}
function getallheaders() : array
{
    \error_clear_last();
    $result = \getallheaders();
    if ($result === \false) {
        throw ApacheException::createFromPhpError();
    }
    return $result;
}
function virtual(string $uri) : void
{
    \error_clear_last();
    $result = \virtual($uri);
    if ($result === \false) {
        throw ApacheException::createFromPhpError();
    }
}
