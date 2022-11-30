<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\InfoException;
function assert_options(int $what, $value = null)
{
    \error_clear_last();
    if ($value !== null) {
        $result = \assert_options($what, $value);
    } else {
        $result = \assert_options($what);
    }
    if ($result === \false) {
        throw InfoException::createFromPhpError();
    }
    return $result;
}
function cli_set_process_title(string $title) : void
{
    \error_clear_last();
    $result = \cli_set_process_title($title);
    if ($result === \false) {
        throw InfoException::createFromPhpError();
    }
}
function dl(string $extension_filename) : void
{
    \error_clear_last();
    $result = \dl($extension_filename);
    if ($result === \false) {
        throw InfoException::createFromPhpError();
    }
}
function get_include_path() : string
{
    \error_clear_last();
    $result = \get_include_path();
    if ($result === \false) {
        throw InfoException::createFromPhpError();
    }
    return $result;
}
function getlastmod() : int
{
    \error_clear_last();
    $result = \getlastmod();
    if ($result === \false) {
        throw InfoException::createFromPhpError();
    }
    return $result;
}
function getmygid() : int
{
    \error_clear_last();
    $result = \getmygid();
    if ($result === \false) {
        throw InfoException::createFromPhpError();
    }
    return $result;
}
function getmyinode() : int
{
    \error_clear_last();
    $result = \getmyinode();
    if ($result === \false) {
        throw InfoException::createFromPhpError();
    }
    return $result;
}
function getmypid() : int
{
    \error_clear_last();
    $result = \getmypid();
    if ($result === \false) {
        throw InfoException::createFromPhpError();
    }
    return $result;
}
function getmyuid() : int
{
    \error_clear_last();
    $result = \getmyuid();
    if ($result === \false) {
        throw InfoException::createFromPhpError();
    }
    return $result;
}
function getopt(string $short_options, array $long_options = [], ?int &$rest_index = null) : array
{
    \error_clear_last();
    $result = \getopt($short_options, $long_options, $rest_index);
    if ($result === \false) {
        throw InfoException::createFromPhpError();
    }
    return $result;
}
function getrusage(int $mode = 0) : array
{
    \error_clear_last();
    $result = \getrusage($mode);
    if ($result === \false) {
        throw InfoException::createFromPhpError();
    }
    return $result;
}
function ini_get(string $option) : string
{
    \error_clear_last();
    $result = \ini_get($option);
    if ($result === \false) {
        throw InfoException::createFromPhpError();
    }
    return $result;
}
function ini_set(string $option, string $value) : string
{
    \error_clear_last();
    $result = \ini_set($option, $value);
    if ($result === \false) {
        throw InfoException::createFromPhpError();
    }
    return $result;
}
function php_sapi_name() : string
{
    \error_clear_last();
    $result = \php_sapi_name();
    if ($result === \false) {
        throw InfoException::createFromPhpError();
    }
    return $result;
}
function phpcredits(int $flags = \CREDITS_ALL) : void
{
    \error_clear_last();
    $result = \phpcredits($flags);
    if ($result === \false) {
        throw InfoException::createFromPhpError();
    }
}
function phpinfo(int $flags = \INFO_ALL) : void
{
    \error_clear_last();
    $result = \phpinfo($flags);
    if ($result === \false) {
        throw InfoException::createFromPhpError();
    }
}
function putenv(string $assignment) : void
{
    \error_clear_last();
    $result = \putenv($assignment);
    if ($result === \false) {
        throw InfoException::createFromPhpError();
    }
}
function set_include_path(string $include_path) : string
{
    \error_clear_last();
    $result = \set_include_path($include_path);
    if ($result === \false) {
        throw InfoException::createFromPhpError();
    }
    return $result;
}
function set_time_limit(int $seconds) : void
{
    \error_clear_last();
    $result = \set_time_limit($seconds);
    if ($result === \false) {
        throw InfoException::createFromPhpError();
    }
}
