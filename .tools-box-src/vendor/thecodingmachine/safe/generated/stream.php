<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\StreamException;
function stream_context_set_params($context, array $params) : void
{
    \error_clear_last();
    $safeResult = \stream_context_set_params($context, $params);
    if ($safeResult === \false) {
        throw StreamException::createFromPhpError();
    }
}
function stream_copy_to_stream($from, $to, int $length = null, int $offset = 0) : int
{
    \error_clear_last();
    if ($offset !== 0) {
        $safeResult = \stream_copy_to_stream($from, $to, $length, $offset);
    } elseif ($length !== null) {
        $safeResult = \stream_copy_to_stream($from, $to, $length);
    } else {
        $safeResult = \stream_copy_to_stream($from, $to);
    }
    if ($safeResult === \false) {
        throw StreamException::createFromPhpError();
    }
    return $safeResult;
}
function stream_filter_append($stream, string $filtername, int $read_write = null, $params = null)
{
    \error_clear_last();
    if ($params !== null) {
        $safeResult = \stream_filter_append($stream, $filtername, $read_write, $params);
    } elseif ($read_write !== null) {
        $safeResult = \stream_filter_append($stream, $filtername, $read_write);
    } else {
        $safeResult = \stream_filter_append($stream, $filtername);
    }
    if ($safeResult === \false) {
        throw StreamException::createFromPhpError();
    }
    return $safeResult;
}
function stream_filter_prepend($stream, string $filtername, int $read_write = null, $params = null)
{
    \error_clear_last();
    if ($params !== null) {
        $safeResult = \stream_filter_prepend($stream, $filtername, $read_write, $params);
    } elseif ($read_write !== null) {
        $safeResult = \stream_filter_prepend($stream, $filtername, $read_write);
    } else {
        $safeResult = \stream_filter_prepend($stream, $filtername);
    }
    if ($safeResult === \false) {
        throw StreamException::createFromPhpError();
    }
    return $safeResult;
}
function stream_filter_register(string $filter_name, string $class) : void
{
    \error_clear_last();
    $safeResult = \stream_filter_register($filter_name, $class);
    if ($safeResult === \false) {
        throw StreamException::createFromPhpError();
    }
}
function stream_filter_remove($stream_filter) : void
{
    \error_clear_last();
    $safeResult = \stream_filter_remove($stream_filter);
    if ($safeResult === \false) {
        throw StreamException::createFromPhpError();
    }
}
function stream_get_contents($stream, int $length = null, int $offset = -1) : string
{
    \error_clear_last();
    if ($offset !== -1) {
        $safeResult = \stream_get_contents($stream, $length, $offset);
    } elseif ($length !== null) {
        $safeResult = \stream_get_contents($stream, $length);
    } else {
        $safeResult = \stream_get_contents($stream);
    }
    if ($safeResult === \false) {
        throw StreamException::createFromPhpError();
    }
    return $safeResult;
}
function stream_get_line($stream, int $length, string $ending = "") : string
{
    \error_clear_last();
    $safeResult = \stream_get_line($stream, $length, $ending);
    if ($safeResult === \false) {
        throw StreamException::createFromPhpError();
    }
    return $safeResult;
}
function stream_isatty($stream) : void
{
    \error_clear_last();
    $safeResult = \stream_isatty($stream);
    if ($safeResult === \false) {
        throw StreamException::createFromPhpError();
    }
}
function stream_resolve_include_path(string $filename) : string
{
    \error_clear_last();
    $safeResult = \stream_resolve_include_path($filename);
    if ($safeResult === \false) {
        throw StreamException::createFromPhpError();
    }
    return $safeResult;
}
function stream_set_blocking($stream, bool $enable) : void
{
    \error_clear_last();
    $safeResult = \stream_set_blocking($stream, $enable);
    if ($safeResult === \false) {
        throw StreamException::createFromPhpError();
    }
}
function stream_set_timeout($stream, int $seconds, int $microseconds = 0) : void
{
    \error_clear_last();
    $safeResult = \stream_set_timeout($stream, $seconds, $microseconds);
    if ($safeResult === \false) {
        throw StreamException::createFromPhpError();
    }
}
function stream_socket_accept($socket, float $timeout = null, ?string &$peer_name = null)
{
    \error_clear_last();
    if ($peer_name !== null) {
        $safeResult = \stream_socket_accept($socket, $timeout, $peer_name);
    } elseif ($timeout !== null) {
        $safeResult = \stream_socket_accept($socket, $timeout);
    } else {
        $safeResult = \stream_socket_accept($socket);
    }
    if ($safeResult === \false) {
        throw StreamException::createFromPhpError();
    }
    return $safeResult;
}
function stream_socket_client(string $address, ?int &$error_code = null, ?string &$error_message = null, float $timeout = null, int $flags = \STREAM_CLIENT_CONNECT, $context = null)
{
    \error_clear_last();
    if ($context !== null) {
        $safeResult = \stream_socket_client($address, $error_code, $error_message, $timeout, $flags, $context);
    } elseif ($flags !== \STREAM_CLIENT_CONNECT) {
        $safeResult = \stream_socket_client($address, $error_code, $error_message, $timeout, $flags);
    } elseif ($timeout !== null) {
        $safeResult = \stream_socket_client($address, $error_code, $error_message, $timeout);
    } else {
        $safeResult = \stream_socket_client($address, $error_code, $error_message);
    }
    if ($safeResult === \false) {
        throw StreamException::createFromPhpError();
    }
    return $safeResult;
}
function stream_socket_get_name($socket, bool $remote) : string
{
    \error_clear_last();
    $safeResult = \stream_socket_get_name($socket, $remote);
    if ($safeResult === \false) {
        throw StreamException::createFromPhpError();
    }
    return $safeResult;
}
function stream_socket_pair(int $domain, int $type, int $protocol) : iterable
{
    \error_clear_last();
    $safeResult = \stream_socket_pair($domain, $type, $protocol);
    if ($safeResult === \false) {
        throw StreamException::createFromPhpError();
    }
    return $safeResult;
}
function stream_socket_recvfrom($socket, int $length, int $flags = 0, ?string &$address = null) : string
{
    \error_clear_last();
    $safeResult = \stream_socket_recvfrom($socket, $length, $flags, $address);
    if ($safeResult === \false) {
        throw StreamException::createFromPhpError();
    }
    return $safeResult;
}
function stream_socket_sendto($socket, string $data, int $flags = 0, string $address = "") : int
{
    \error_clear_last();
    $safeResult = \stream_socket_sendto($socket, $data, $flags, $address);
    if ($safeResult === \false) {
        throw StreamException::createFromPhpError();
    }
    return $safeResult;
}
function stream_socket_server(string $address, ?int &$error_code = null, ?string &$error_message = null, int $flags = \STREAM_SERVER_BIND | \STREAM_SERVER_LISTEN, $context = null)
{
    \error_clear_last();
    if ($context !== null) {
        $safeResult = \stream_socket_server($address, $error_code, $error_message, $flags, $context);
    } else {
        $safeResult = \stream_socket_server($address, $error_code, $error_message, $flags);
    }
    if ($safeResult === \false) {
        throw StreamException::createFromPhpError();
    }
    return $safeResult;
}
function stream_socket_shutdown($stream, int $mode) : void
{
    \error_clear_last();
    $safeResult = \stream_socket_shutdown($stream, $mode);
    if ($safeResult === \false) {
        throw StreamException::createFromPhpError();
    }
}
function stream_supports_lock($stream) : void
{
    \error_clear_last();
    $safeResult = \stream_supports_lock($stream);
    if ($safeResult === \false) {
        throw StreamException::createFromPhpError();
    }
}
function stream_wrapper_register(string $protocol, string $class, int $flags = 0) : void
{
    \error_clear_last();
    $safeResult = \stream_wrapper_register($protocol, $class, $flags);
    if ($safeResult === \false) {
        throw StreamException::createFromPhpError();
    }
}
function stream_wrapper_restore(string $protocol) : void
{
    \error_clear_last();
    $safeResult = \stream_wrapper_restore($protocol);
    if ($safeResult === \false) {
        throw StreamException::createFromPhpError();
    }
}
function stream_wrapper_unregister(string $protocol) : void
{
    \error_clear_last();
    $safeResult = \stream_wrapper_unregister($protocol);
    if ($safeResult === \false) {
        throw StreamException::createFromPhpError();
    }
}
