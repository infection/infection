<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\SocketsException;
function socket_accept($socket)
{
    \error_clear_last();
    $safeResult = \socket_accept($socket);
    if ($safeResult === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $safeResult;
}
function socket_addrinfo_bind($address)
{
    \error_clear_last();
    $safeResult = \socket_addrinfo_bind($address);
    if ($safeResult === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $safeResult;
}
function socket_addrinfo_connect($address)
{
    \error_clear_last();
    $safeResult = \socket_addrinfo_connect($address);
    if ($safeResult === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $safeResult;
}
function socket_addrinfo_lookup(string $host, $service = null, array $hints = []) : iterable
{
    \error_clear_last();
    if ($hints !== []) {
        $safeResult = \socket_addrinfo_lookup($host, $service, $hints);
    } elseif ($service !== null) {
        $safeResult = \socket_addrinfo_lookup($host, $service);
    } else {
        $safeResult = \socket_addrinfo_lookup($host);
    }
    if ($safeResult === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $safeResult;
}
function socket_bind($socket, string $address, int $port = 0) : void
{
    \error_clear_last();
    $safeResult = \socket_bind($socket, $address, $port);
    if ($safeResult === \false) {
        throw SocketsException::createFromPhpError();
    }
}
function socket_connect($socket, string $address, int $port = null) : void
{
    \error_clear_last();
    if ($port !== null) {
        $safeResult = \socket_connect($socket, $address, $port);
    } else {
        $safeResult = \socket_connect($socket, $address);
    }
    if ($safeResult === \false) {
        throw SocketsException::createFromPhpError();
    }
}
function socket_create_listen(int $port, int $backlog = 128)
{
    \error_clear_last();
    $safeResult = \socket_create_listen($port, $backlog);
    if ($safeResult === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $safeResult;
}
function socket_create_pair(int $domain, int $type, int $protocol, ?iterable &$pair) : void
{
    \error_clear_last();
    $safeResult = \socket_create_pair($domain, $type, $protocol, $pair);
    if ($safeResult === \false) {
        throw SocketsException::createFromPhpError();
    }
}
function socket_create(int $domain, int $type, int $protocol)
{
    \error_clear_last();
    $safeResult = \socket_create($domain, $type, $protocol);
    if ($safeResult === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $safeResult;
}
function socket_export_stream($socket)
{
    \error_clear_last();
    $safeResult = \socket_export_stream($socket);
    if ($safeResult === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $safeResult;
}
function socket_get_option($socket, int $level, int $option)
{
    \error_clear_last();
    $safeResult = \socket_get_option($socket, $level, $option);
    if ($safeResult === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $safeResult;
}
function socket_getpeername($socket, ?string &$address, ?int &$port = null) : void
{
    \error_clear_last();
    $safeResult = \socket_getpeername($socket, $address, $port);
    if ($safeResult === \false) {
        throw SocketsException::createFromPhpError();
    }
}
function socket_getsockname($socket, ?string &$address, ?int &$port = null) : void
{
    \error_clear_last();
    $safeResult = \socket_getsockname($socket, $address, $port);
    if ($safeResult === \false) {
        throw SocketsException::createFromPhpError();
    }
}
function socket_import_stream($stream)
{
    \error_clear_last();
    $safeResult = \socket_import_stream($stream);
    if ($safeResult === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $safeResult;
}
function socket_listen($socket, int $backlog = 0) : void
{
    \error_clear_last();
    $safeResult = \socket_listen($socket, $backlog);
    if ($safeResult === \false) {
        throw SocketsException::createFromPhpError();
    }
}
function socket_read($socket, int $length, int $mode = \PHP_BINARY_READ) : string
{
    \error_clear_last();
    $safeResult = \socket_read($socket, $length, $mode);
    if ($safeResult === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $safeResult;
}
function socket_send($socket, string $data, int $length, int $flags) : int
{
    \error_clear_last();
    $safeResult = \socket_send($socket, $data, $length, $flags);
    if ($safeResult === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $safeResult;
}
function socket_sendmsg($socket, array $message, int $flags = 0) : int
{
    \error_clear_last();
    $safeResult = \socket_sendmsg($socket, $message, $flags);
    if ($safeResult === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $safeResult;
}
function socket_sendto($socket, string $data, int $length, int $flags, string $address, int $port = null) : int
{
    \error_clear_last();
    if ($port !== null) {
        $safeResult = \socket_sendto($socket, $data, $length, $flags, $address, $port);
    } else {
        $safeResult = \socket_sendto($socket, $data, $length, $flags, $address);
    }
    if ($safeResult === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $safeResult;
}
function socket_set_block($socket) : void
{
    \error_clear_last();
    $safeResult = \socket_set_block($socket);
    if ($safeResult === \false) {
        throw SocketsException::createFromPhpError();
    }
}
function socket_set_nonblock($socket) : void
{
    \error_clear_last();
    $safeResult = \socket_set_nonblock($socket);
    if ($safeResult === \false) {
        throw SocketsException::createFromPhpError();
    }
}
function socket_set_option($socket, int $level, int $option, $value) : void
{
    \error_clear_last();
    $safeResult = \socket_set_option($socket, $level, $option, $value);
    if ($safeResult === \false) {
        throw SocketsException::createFromPhpError();
    }
}
function socket_shutdown($socket, int $mode = 2) : void
{
    \error_clear_last();
    $safeResult = \socket_shutdown($socket, $mode);
    if ($safeResult === \false) {
        throw SocketsException::createFromPhpError();
    }
}
function socket_wsaprotocol_info_export($socket, int $process_id) : string
{
    \error_clear_last();
    $safeResult = \socket_wsaprotocol_info_export($socket, $process_id);
    if ($safeResult === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $safeResult;
}
function socket_wsaprotocol_info_import(string $info_id)
{
    \error_clear_last();
    $safeResult = \socket_wsaprotocol_info_import($info_id);
    if ($safeResult === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $safeResult;
}
function socket_wsaprotocol_info_release(string $info_id) : void
{
    \error_clear_last();
    $safeResult = \socket_wsaprotocol_info_release($info_id);
    if ($safeResult === \false) {
        throw SocketsException::createFromPhpError();
    }
}
