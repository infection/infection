<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\StreamException;
function stream_context_set_params($context, array $params) : void
{
    \error_clear_last();
    $result = \stream_context_set_params($context, $params);
    if ($result === \false) {
        throw StreamException::createFromPhpError();
    }
}
function stream_copy_to_stream($from, $to, int $length = null, int $offset = 0) : int
{
    \error_clear_last();
    if ($offset !== 0) {
        $result = \stream_copy_to_stream($from, $to, $length, $offset);
    } elseif ($length !== null) {
        $result = \stream_copy_to_stream($from, $to, $length);
    } else {
        $result = \stream_copy_to_stream($from, $to);
    }
    if ($result === \false) {
        throw StreamException::createFromPhpError();
    }
    return $result;
}
function stream_filter_append($stream, string $filtername, int $read_write = null, array $params = null)
{
    \error_clear_last();
    if ($params !== null) {
        $result = \stream_filter_append($stream, $filtername, $read_write, $params);
    } elseif ($read_write !== null) {
        $result = \stream_filter_append($stream, $filtername, $read_write);
    } else {
        $result = \stream_filter_append($stream, $filtername);
    }
    if ($result === \false) {
        throw StreamException::createFromPhpError();
    }
    return $result;
}
function stream_filter_prepend($stream, string $filtername, int $read_write = null, array $params = null)
{
    \error_clear_last();
    if ($params !== null) {
        $result = \stream_filter_prepend($stream, $filtername, $read_write, $params);
    } elseif ($read_write !== null) {
        $result = \stream_filter_prepend($stream, $filtername, $read_write);
    } else {
        $result = \stream_filter_prepend($stream, $filtername);
    }
    if ($result === \false) {
        throw StreamException::createFromPhpError();
    }
    return $result;
}
function stream_filter_register(string $filter_name, string $class) : void
{
    \error_clear_last();
    $result = \stream_filter_register($filter_name, $class);
    if ($result === \false) {
        throw StreamException::createFromPhpError();
    }
}
function stream_filter_remove($stream_filter) : void
{
    \error_clear_last();
    $result = \stream_filter_remove($stream_filter);
    if ($result === \false) {
        throw StreamException::createFromPhpError();
    }
}
function stream_get_contents($stream, int $length = null, int $offset = -1) : string
{
    \error_clear_last();
    if ($offset !== -1) {
        $result = \stream_get_contents($stream, $length, $offset);
    } elseif ($length !== null) {
        $result = \stream_get_contents($stream, $length);
    } else {
        $result = \stream_get_contents($stream);
    }
    if ($result === \false) {
        throw StreamException::createFromPhpError();
    }
    return $result;
}
function stream_get_line($stream, int $length, string $ending = "") : string
{
    \error_clear_last();
    $result = \stream_get_line($stream, $length, $ending);
    if ($result === \false) {
        throw StreamException::createFromPhpError();
    }
    return $result;
}
function stream_isatty($stream) : void
{
    \error_clear_last();
    $result = \stream_isatty($stream);
    if ($result === \false) {
        throw StreamException::createFromPhpError();
    }
}
function stream_resolve_include_path(string $filename) : string
{
    \error_clear_last();
    $result = \stream_resolve_include_path($filename);
    if ($result === \false) {
        throw StreamException::createFromPhpError();
    }
    return $result;
}
function stream_set_blocking($stream, bool $enable) : void
{
    \error_clear_last();
    $result = \stream_set_blocking($stream, $enable);
    if ($result === \false) {
        throw StreamException::createFromPhpError();
    }
}
function stream_set_timeout($stream, int $seconds, int $microseconds = 0) : void
{
    \error_clear_last();
    $result = \stream_set_timeout($stream, $seconds, $microseconds);
    if ($result === \false) {
        throw StreamException::createFromPhpError();
    }
}
function stream_socket_accept($socket, float $timeout = null, ?string &$peer_name = null)
{
    \error_clear_last();
    if ($peer_name !== null) {
        $result = \stream_socket_accept($socket, $timeout, $peer_name);
    } elseif ($timeout !== null) {
        $result = \stream_socket_accept($socket, $timeout);
    } else {
        $result = \stream_socket_accept($socket);
    }
    if ($result === \false) {
        throw StreamException::createFromPhpError();
    }
    return $result;
}
function stream_socket_client(string $address, ?int &$error_code = null, ?string &$error_message = null, float $timeout = null, int $flags = \STREAM_CLIENT_CONNECT, $context = null)
{
    \error_clear_last();
    if ($context !== null) {
        $result = \stream_socket_client($address, $error_code, $error_message, $timeout, $flags, $context);
    } elseif ($flags !== \STREAM_CLIENT_CONNECT) {
        $result = \stream_socket_client($address, $error_code, $error_message, $timeout, $flags);
    } elseif ($timeout !== null) {
        $result = \stream_socket_client($address, $error_code, $error_message, $timeout);
    } else {
        $result = \stream_socket_client($address, $error_code, $error_message);
    }
    if ($result === \false) {
        throw StreamException::createFromPhpError();
    }
    return $result;
}
function stream_socket_get_name($socket, bool $remote) : string
{
    \error_clear_last();
    $result = \stream_socket_get_name($socket, $remote);
    if ($result === \false) {
        throw StreamException::createFromPhpError();
    }
    return $result;
}
function stream_socket_pair(int $domain, int $type, int $protocol) : iterable
{
    \error_clear_last();
    $result = \stream_socket_pair($domain, $type, $protocol);
    if ($result === \false) {
        throw StreamException::createFromPhpError();
    }
    return $result;
}
function stream_socket_recvfrom($socket, int $length, int $flags = 0, ?string &$address = null) : string
{
    \error_clear_last();
    $result = \stream_socket_recvfrom($socket, $length, $flags, $address);
    if ($result === \false) {
        throw StreamException::createFromPhpError();
    }
    return $result;
}
function stream_socket_sendto($socket, string $data, int $flags = 0, string $address = "") : int
{
    \error_clear_last();
    $result = \stream_socket_sendto($socket, $data, $flags, $address);
    if ($result === \false) {
        throw StreamException::createFromPhpError();
    }
    return $result;
}
function stream_socket_server(string $address, ?int &$error_code = null, ?string &$error_message = null, int $flags = \STREAM_SERVER_BIND | \STREAM_SERVER_LISTEN, $context = null)
{
    \error_clear_last();
    if ($context !== null) {
        $result = \stream_socket_server($address, $error_code, $error_message, $flags, $context);
    } else {
        $result = \stream_socket_server($address, $error_code, $error_message, $flags);
    }
    if ($result === \false) {
        throw StreamException::createFromPhpError();
    }
    return $result;
}
function stream_socket_shutdown($stream, int $mode) : void
{
    \error_clear_last();
    $result = \stream_socket_shutdown($stream, $mode);
    if ($result === \false) {
        throw StreamException::createFromPhpError();
    }
}
function stream_supports_lock($stream) : void
{
    \error_clear_last();
    $result = \stream_supports_lock($stream);
    if ($result === \false) {
        throw StreamException::createFromPhpError();
    }
}
function stream_wrapper_register(string $protocol, string $class, int $flags = 0) : void
{
    \error_clear_last();
    $result = \stream_wrapper_register($protocol, $class, $flags);
    if ($result === \false) {
        throw StreamException::createFromPhpError();
    }
}
function stream_wrapper_restore(string $protocol) : void
{
    \error_clear_last();
    $result = \stream_wrapper_restore($protocol);
    if ($result === \false) {
        throw StreamException::createFromPhpError();
    }
}
function stream_wrapper_unregister(string $protocol) : void
{
    \error_clear_last();
    $result = \stream_wrapper_unregister($protocol);
    if ($result === \false) {
        throw StreamException::createFromPhpError();
    }
}
