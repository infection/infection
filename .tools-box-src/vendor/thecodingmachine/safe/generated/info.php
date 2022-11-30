<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\InfoException;
function assert_options(int $what, $value = null)
{
    \error_clear_last();
    if ($value !== null) {
        $safeResult = \assert_options($what, $value);
    } else {
        $safeResult = \assert_options($what);
    }
    if ($safeResult === \false) {
        throw InfoException::createFromPhpError();
    }
    return $safeResult;
}
function cli_set_process_title(string $title) : void
{
    \error_clear_last();
    $safeResult = \cli_set_process_title($title);
    if ($safeResult === \false) {
        throw InfoException::createFromPhpError();
    }
}
function dl(string $extension_filename) : void
{
    \error_clear_last();
    $safeResult = \dl($extension_filename);
    if ($safeResult === \false) {
        throw InfoException::createFromPhpError();
    }
}
function get_include_path() : string
{
    \error_clear_last();
    $safeResult = \get_include_path();
    if ($safeResult === \false) {
        throw InfoException::createFromPhpError();
    }
    return $safeResult;
}
function getlastmod() : int
{
    \error_clear_last();
    $safeResult = \getlastmod();
    if ($safeResult === \false) {
        throw InfoException::createFromPhpError();
    }
    return $safeResult;
}
function getmygid() : int
{
    \error_clear_last();
    $safeResult = \getmygid();
    if ($safeResult === \false) {
        throw InfoException::createFromPhpError();
    }
    return $safeResult;
}
function getmyinode() : int
{
    \error_clear_last();
    $safeResult = \getmyinode();
    if ($safeResult === \false) {
        throw InfoException::createFromPhpError();
    }
    return $safeResult;
}
function getmypid() : int
{
    \error_clear_last();
    $safeResult = \getmypid();
    if ($safeResult === \false) {
        throw InfoException::createFromPhpError();
    }
    return $safeResult;
}
function getmyuid() : int
{
    \error_clear_last();
    $safeResult = \getmyuid();
    if ($safeResult === \false) {
        throw InfoException::createFromPhpError();
    }
    return $safeResult;
}
function getopt(string $short_options, array $long_options = [], ?int &$rest_index = null) : array
{
    \error_clear_last();
    $safeResult = \getopt($short_options, $long_options, $rest_index);
    if ($safeResult === \false) {
        throw InfoException::createFromPhpError();
    }
    return $safeResult;
}
function getrusage(int $mode = 0) : array
{
    \error_clear_last();
    $safeResult = \getrusage($mode);
    if ($safeResult === \false) {
        throw InfoException::createFromPhpError();
    }
    return $safeResult;
}
function ini_get(string $option) : string
{
    \error_clear_last();
    $safeResult = \ini_get($option);
    if ($safeResult === \false) {
        throw InfoException::createFromPhpError();
    }
    return $safeResult;
}
function ini_set(string $option, string $value) : string
{
    \error_clear_last();
    $safeResult = \ini_set($option, $value);
    if ($safeResult === \false) {
        throw InfoException::createFromPhpError();
    }
    return $safeResult;
}
function php_sapi_name() : string
{
    \error_clear_last();
    $safeResult = \php_sapi_name();
    if ($safeResult === \false) {
        throw InfoException::createFromPhpError();
    }
    return $safeResult;
}
function phpcredits(int $flags = \CREDITS_ALL) : void
{
    \error_clear_last();
    $safeResult = \phpcredits($flags);
    if ($safeResult === \false) {
        throw InfoException::createFromPhpError();
    }
}
function phpinfo(int $flags = \INFO_ALL) : void
{
    \error_clear_last();
    $safeResult = \phpinfo($flags);
    if ($safeResult === \false) {
        throw InfoException::createFromPhpError();
    }
}
function putenv(string $assignment) : void
{
    \error_clear_last();
    $safeResult = \putenv($assignment);
    if ($safeResult === \false) {
        throw InfoException::createFromPhpError();
    }
}
function set_include_path(string $include_path) : string
{
    \error_clear_last();
    $safeResult = \set_include_path($include_path);
    if ($safeResult === \false) {
        throw InfoException::createFromPhpError();
    }
    return $safeResult;
}
function set_time_limit(int $seconds) : void
{
    \error_clear_last();
    $safeResult = \set_time_limit($seconds);
    if ($safeResult === \false) {
        throw InfoException::createFromPhpError();
    }
}
