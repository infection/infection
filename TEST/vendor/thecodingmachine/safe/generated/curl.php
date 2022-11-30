<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\CurlException;
function curl_copy_handle(\CurlHandle $handle) : \CurlHandle
{
    \error_clear_last();
    $result = \curl_copy_handle($handle);
    if ($result === \false) {
        throw CurlException::createFromPhpError();
    }
    return $result;
}
function curl_escape(\CurlHandle $handle, string $string) : string
{
    \error_clear_last();
    $result = \curl_escape($handle, $string);
    if ($result === \false) {
        throw CurlException::createFromPhpError();
    }
    return $result;
}
function curl_exec(\CurlHandle $handle)
{
    \error_clear_last();
    $result = \curl_exec($handle);
    if ($result === \false) {
        throw CurlException::createFromPhpError();
    }
    return $result;
}
function curl_getinfo(\CurlHandle $handle, int $option = null)
{
    \error_clear_last();
    if ($option !== null) {
        $result = \curl_getinfo($handle, $option);
    } else {
        $result = \curl_getinfo($handle);
    }
    if ($result === \false) {
        throw CurlException::createFromPhpError();
    }
    return $result;
}
function curl_init(string $url = null) : \CurlHandle
{
    \error_clear_last();
    if ($url !== null) {
        $result = \curl_init($url);
    } else {
        $result = \curl_init();
    }
    if ($result === \false) {
        throw CurlException::createFromPhpError();
    }
    return $result;
}
function curl_multi_info_read(\CurlMultiHandle $multi_handle, ?int &$queued_messages = null) : array
{
    \error_clear_last();
    $result = \curl_multi_info_read($multi_handle, $queued_messages);
    if ($result === \false) {
        throw CurlException::createFromPhpError();
    }
    return $result;
}
function curl_multi_init() : \CurlMultiHandle
{
    \error_clear_last();
    $result = \curl_multi_init();
    if ($result === \false) {
        throw CurlException::createFromPhpError();
    }
    return $result;
}
function curl_multi_setopt(\CurlMultiHandle $multi_handle, int $option, $value) : void
{
    \error_clear_last();
    $result = \curl_multi_setopt($multi_handle, $option, $value);
    if ($result === \false) {
        throw CurlException::createFromPhpError();
    }
}
function curl_setopt(\CurlHandle $handle, int $option, $value) : void
{
    \error_clear_last();
    $result = \curl_setopt($handle, $option, $value);
    if ($result === \false) {
        throw CurlException::createFromPhpError();
    }
}
function curl_share_errno(\CurlShareHandle $share_handle) : int
{
    \error_clear_last();
    $result = \curl_share_errno($share_handle);
    if ($result === \false) {
        throw CurlException::createFromPhpError();
    }
    return $result;
}
function curl_share_setopt(\CurlShareHandle $share_handle, int $option, $value) : void
{
    \error_clear_last();
    $result = \curl_share_setopt($share_handle, $option, $value);
    if ($result === \false) {
        throw CurlException::createFromPhpError();
    }
}
function curl_unescape(\CurlHandle $handle, string $string) : string
{
    \error_clear_last();
    $result = \curl_unescape($handle, $string);
    if ($result === \false) {
        throw CurlException::createFromPhpError();
    }
    return $result;
}
