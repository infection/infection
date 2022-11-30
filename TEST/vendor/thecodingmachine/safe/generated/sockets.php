<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\SocketsException;
function socket_accept($socket)
{
    \error_clear_last();
    $result = \socket_accept($socket);
    if ($result === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $result;
}
function socket_addrinfo_bind($address)
{
    \error_clear_last();
    $result = \socket_addrinfo_bind($address);
    if ($result === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $result;
}
function socket_addrinfo_connect($address)
{
    \error_clear_last();
    $result = \socket_addrinfo_connect($address);
    if ($result === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $result;
}
function socket_addrinfo_lookup(string $host, $service = null, array $hints = []) : iterable
{
    \error_clear_last();
    if ($hints !== []) {
        $result = \socket_addrinfo_lookup($host, $service, $hints);
    } elseif ($service !== null) {
        $result = \socket_addrinfo_lookup($host, $service);
    } else {
        $result = \socket_addrinfo_lookup($host);
    }
    if ($result === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $result;
}
function socket_bind($socket, string $address, int $port = 0) : void
{
    \error_clear_last();
    $result = \socket_bind($socket, $address, $port);
    if ($result === \false) {
        throw SocketsException::createFromPhpError();
    }
}
function socket_connect($socket, string $address, int $port = null) : void
{
    \error_clear_last();
    if ($port !== null) {
        $result = \socket_connect($socket, $address, $port);
    } else {
        $result = \socket_connect($socket, $address);
    }
    if ($result === \false) {
        throw SocketsException::createFromPhpError();
    }
}
function socket_create_listen(int $port, int $backlog = 128)
{
    \error_clear_last();
    $result = \socket_create_listen($port, $backlog);
    if ($result === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $result;
}
function socket_create_pair(int $domain, int $type, int $protocol, ?iterable &$pair) : void
{
    \error_clear_last();
    $result = \socket_create_pair($domain, $type, $protocol, $pair);
    if ($result === \false) {
        throw SocketsException::createFromPhpError();
    }
}
function socket_create(int $domain, int $type, int $protocol)
{
    \error_clear_last();
    $result = \socket_create($domain, $type, $protocol);
    if ($result === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $result;
}
function socket_export_stream($socket)
{
    \error_clear_last();
    $result = \socket_export_stream($socket);
    if ($result === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $result;
}
function socket_get_option($socket, int $level, int $option)
{
    \error_clear_last();
    $result = \socket_get_option($socket, $level, $option);
    if ($result === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $result;
}
function socket_getpeername($socket, ?string &$address, ?int &$port = null) : void
{
    \error_clear_last();
    $result = \socket_getpeername($socket, $address, $port);
    if ($result === \false) {
        throw SocketsException::createFromPhpError();
    }
}
function socket_getsockname($socket, ?string &$address, ?int &$port = null) : void
{
    \error_clear_last();
    $result = \socket_getsockname($socket, $address, $port);
    if ($result === \false) {
        throw SocketsException::createFromPhpError();
    }
}
function socket_import_stream($stream)
{
    \error_clear_last();
    $result = \socket_import_stream($stream);
    if ($result === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $result;
}
function socket_listen($socket, int $backlog = 0) : void
{
    \error_clear_last();
    $result = \socket_listen($socket, $backlog);
    if ($result === \false) {
        throw SocketsException::createFromPhpError();
    }
}
function socket_read($socket, int $length, int $mode = \PHP_BINARY_READ) : string
{
    \error_clear_last();
    $result = \socket_read($socket, $length, $mode);
    if ($result === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $result;
}
function socket_send($socket, string $data, int $length, int $flags) : int
{
    \error_clear_last();
    $result = \socket_send($socket, $data, $length, $flags);
    if ($result === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $result;
}
function socket_sendmsg($socket, array $message, int $flags = 0) : int
{
    \error_clear_last();
    $result = \socket_sendmsg($socket, $message, $flags);
    if ($result === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $result;
}
function socket_sendto($socket, string $data, int $length, int $flags, string $address, int $port = null) : int
{
    \error_clear_last();
    if ($port !== null) {
        $result = \socket_sendto($socket, $data, $length, $flags, $address, $port);
    } else {
        $result = \socket_sendto($socket, $data, $length, $flags, $address);
    }
    if ($result === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $result;
}
function socket_set_block($socket) : void
{
    \error_clear_last();
    $result = \socket_set_block($socket);
    if ($result === \false) {
        throw SocketsException::createFromPhpError();
    }
}
function socket_set_nonblock($socket) : void
{
    \error_clear_last();
    $result = \socket_set_nonblock($socket);
    if ($result === \false) {
        throw SocketsException::createFromPhpError();
    }
}
function socket_set_option($socket, int $level, int $option, $value) : void
{
    \error_clear_last();
    $result = \socket_set_option($socket, $level, $option, $value);
    if ($result === \false) {
        throw SocketsException::createFromPhpError();
    }
}
function socket_shutdown($socket, int $mode = 2) : void
{
    \error_clear_last();
    $result = \socket_shutdown($socket, $mode);
    if ($result === \false) {
        throw SocketsException::createFromPhpError();
    }
}
function socket_wsaprotocol_info_export($socket, int $process_id) : string
{
    \error_clear_last();
    $result = \socket_wsaprotocol_info_export($socket, $process_id);
    if ($result === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $result;
}
function socket_wsaprotocol_info_import(string $info_id)
{
    \error_clear_last();
    $result = \socket_wsaprotocol_info_import($info_id);
    if ($result === \false) {
        throw SocketsException::createFromPhpError();
    }
    return $result;
}
function socket_wsaprotocol_info_release(string $info_id) : void
{
    \error_clear_last();
    $result = \socket_wsaprotocol_info_release($info_id);
    if ($result === \false) {
        throw SocketsException::createFromPhpError();
    }
}
