<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\SwooleException;
function swoole_async_dns_lookup(string $hostname, callable $callback) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\swoole_async_dns_lookup($hostname, $callback);
    if ($result === \false) {
        throw SwooleException::createFromPhpError();
    }
}
function swoole_async_readfile(string $filename, string $callback) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\swoole_async_readfile($filename, $callback);
    if ($result === \false) {
        throw SwooleException::createFromPhpError();
    }
}
function swoole_async_write(string $filename, string $content, int $offset = null, callable $callback = null) : void
{
    \error_clear_last();
    if ($callback !== null) {
        $result = \_HumbugBox9658796bb9f0\swoole_async_write($filename, $content, $offset, $callback);
    } elseif ($offset !== null) {
        $result = \_HumbugBox9658796bb9f0\swoole_async_write($filename, $content, $offset);
    } else {
        $result = \_HumbugBox9658796bb9f0\swoole_async_write($filename, $content);
    }
    if ($result === \false) {
        throw SwooleException::createFromPhpError();
    }
}
function swoole_async_writefile(string $filename, string $content, callable $callback = null, int $flags = 0) : void
{
    \error_clear_last();
    if ($flags !== 0) {
        $result = \_HumbugBox9658796bb9f0\swoole_async_writefile($filename, $content, $callback, $flags);
    } elseif ($callback !== null) {
        $result = \_HumbugBox9658796bb9f0\swoole_async_writefile($filename, $content, $callback);
    } else {
        $result = \_HumbugBox9658796bb9f0\swoole_async_writefile($filename, $content);
    }
    if ($result === \false) {
        throw SwooleException::createFromPhpError();
    }
}
function swoole_event_defer(callable $callback) : void
{
    \error_clear_last();
    $result = \swoole_event_defer($callback);
    if ($result === \false) {
        throw SwooleException::createFromPhpError();
    }
}
function swoole_event_del(int $fd) : void
{
    \error_clear_last();
    $result = \swoole_event_del($fd);
    if ($result === \false) {
        throw SwooleException::createFromPhpError();
    }
}
function swoole_event_write(int $fd, string $data) : void
{
    \error_clear_last();
    $result = \swoole_event_write($fd, $data);
    if ($result === \false) {
        throw SwooleException::createFromPhpError();
    }
}
