<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\FilesystemException;
use const PREG_NO_ERROR;
use _HumbugBoxb47773b41c19\Safe\Exceptions\MiscException;
use _HumbugBoxb47773b41c19\Safe\Exceptions\PosixException;
use _HumbugBoxb47773b41c19\Safe\Exceptions\SocketsException;
use _HumbugBoxb47773b41c19\Safe\Exceptions\ApcException;
use _HumbugBoxb47773b41c19\Safe\Exceptions\ApcuException;
use _HumbugBoxb47773b41c19\Safe\Exceptions\JsonException;
use _HumbugBoxb47773b41c19\Safe\Exceptions\OpensslException;
use _HumbugBoxb47773b41c19\Safe\Exceptions\PcreException;
use _HumbugBoxb47773b41c19\Safe\Exceptions\SimplexmlException;
function json_decode(string $json, bool $assoc = \false, int $depth = 512, int $flags = 0) : mixed
{
    $data = \json_decode($json, $assoc, $depth, $flags);
    if (\JSON_ERROR_NONE !== \json_last_error()) {
        throw JsonException::createFromPhpError();
    }
    return $data;
}
function apc_fetch($key)
{
    \error_clear_last();
    $result = \apc_fetch($key, $success);
    if ($success === \false) {
        throw ApcException::createFromPhpError();
    }
    return $result;
}
function apcu_fetch($key)
{
    \error_clear_last();
    $result = \apcu_fetch($key, $success);
    if ($success === \false) {
        throw ApcuException::createFromPhpError();
    }
    return $result;
}
function preg_replace($pattern, $replacement, $subject, int $limit = -1, int &$count = null)
{
    \error_clear_last();
    $result = \preg_replace($pattern, $replacement, $subject, $limit, $count);
    if (\preg_last_error() !== PREG_NO_ERROR || $result === null) {
        throw PcreException::createFromPhpError();
    }
    return $result;
}
function readdir($dir_handle = null)
{
    if ($dir_handle !== null) {
        $result = \readdir($dir_handle);
    } else {
        $result = \readdir();
    }
    return $result;
}
function openssl_encrypt(string $data, string $method, string $key, int $options = 0, string $iv = "", string &$tag = "", string $aad = "", int $tag_length = 16) : string
{
    \error_clear_last();
    if (\func_num_args() <= 5) {
        $result = \openssl_encrypt($data, $method, $key, $options, $iv);
    } else {
        $result = \openssl_encrypt($data, $method, $key, $options, $iv, $tag, $aad, $tag_length);
    }
    if ($result === \false) {
        throw OpensslException::createFromPhpError();
    }
    return $result;
}
function socket_write(\Socket $socket, string $buffer, int $length = 0) : int
{
    \error_clear_last();
    $result = $length === 0 ? \socket_write($socket, $buffer) : \socket_write($socket, $buffer, $length);
    if ($result === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $result;
}
function simplexml_import_dom(\DOMNode $node, string $class_name = \SimpleXMLElement::class) : \SimpleXMLElement
{
    \error_clear_last();
    $result = \simplexml_import_dom($node, $class_name);
    if ($result === null) {
        throw SimplexmlException::createFromPhpError();
    }
    return $result;
}
function simplexml_load_file(string $filename, string $class_name = \SimpleXMLElement::class, int $options = 0, string $namespace_or_prefix = "", bool $is_prefix = \false) : \SimpleXMLElement
{
    \error_clear_last();
    $result = \simplexml_load_file($filename, $class_name, $options, $namespace_or_prefix, $is_prefix);
    if ($result === \false) {
        throw SimplexmlException::createFromPhpError();
    }
    return $result;
}
function simplexml_load_string(string $data, string $class_name = \SimpleXMLElement::class, int $options = 0, string $namespace_or_prefix = "", bool $is_prefix = \false) : \SimpleXMLElement
{
    \error_clear_last();
    $result = \simplexml_load_string($data, $class_name, $options, $namespace_or_prefix, $is_prefix);
    if ($result === \false) {
        throw SimplexmlException::createFromPhpError();
    }
    return $result;
}
function sys_getloadavg() : array
{
    \error_clear_last();
    $result = \sys_getloadavg();
    if ($result === \false) {
        throw MiscException::createFromPhpError();
    }
    return $result;
}
function posix_getpgid(int $process_id) : int
{
    \error_clear_last();
    $result = \posix_getpgid($process_id);
    if ($result === \false) {
        throw PosixException::createFromPhpError();
    }
    return $result;
}
/**
@phpstan-param(scalar|\Stringable|null)[] $fields
*/
function fputcsv($stream, array $fields, string $separator = ",", string $enclosure = "\"", string $escape = "\\", string $eol = "\n") : int
{
    \error_clear_last();
    if (\PHP_VERSION_ID >= 80100) {
        /**
        @phpstan-ignore-next-line */
        $result = \fputcsv($stream, $fields, $separator, $enclosure, $escape, $eol);
    } else {
        $result = \fputcsv($stream, $fields, $separator, $enclosure, $escape);
    }
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $result;
}
function fgetcsv($stream, int $length = null, string $separator = ",", string $enclosure = "\"", string $escape = "\\") : array|false
{
    \error_clear_last();
    $safeResult = \fgetcsv($stream, $length, $separator, $enclosure, $escape);
    if ($safeResult === \false && \feof($stream) === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $safeResult;
}
