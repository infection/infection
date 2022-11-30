<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\NetworkException;
function closelog() : void
{
    \error_clear_last();
    $result = \closelog();
    if ($result === \false) {
        throw NetworkException::createFromPhpError();
    }
}
function dns_get_record(string $hostname, int $type = \DNS_ANY, ?array &$authoritative_name_servers = null, ?array &$additional_records = null, bool $raw = \false) : array
{
    \error_clear_last();
    $result = \dns_get_record($hostname, $type, $authoritative_name_servers, $additional_records, $raw);
    if ($result === \false) {
        throw NetworkException::createFromPhpError();
    }
    return $result;
}
function fsockopen(string $hostname, int $port = -1, ?int &$error_code = null, ?string &$error_message = null, float $timeout = null)
{
    \error_clear_last();
    if ($timeout !== null) {
        $result = \fsockopen($hostname, $port, $error_code, $error_message, $timeout);
    } else {
        $result = \fsockopen($hostname, $port, $error_code, $error_message);
    }
    if ($result === \false) {
        throw NetworkException::createFromPhpError();
    }
    return $result;
}
function gethostname() : string
{
    \error_clear_last();
    $result = \gethostname();
    if ($result === \false) {
        throw NetworkException::createFromPhpError();
    }
    return $result;
}
function getprotobyname(string $protocol) : int
{
    \error_clear_last();
    $result = \getprotobyname($protocol);
    if ($result === \false) {
        throw NetworkException::createFromPhpError();
    }
    return $result;
}
function getprotobynumber(int $protocol) : string
{
    \error_clear_last();
    $result = \getprotobynumber($protocol);
    if ($result === \false) {
        throw NetworkException::createFromPhpError();
    }
    return $result;
}
function getservbyport(int $port, string $protocol) : string
{
    \error_clear_last();
    $result = \getservbyport($port, $protocol);
    if ($result === \false) {
        throw NetworkException::createFromPhpError();
    }
    return $result;
}
function header_register_callback(callable $callback) : void
{
    \error_clear_last();
    $result = \header_register_callback($callback);
    if ($result === \false) {
        throw NetworkException::createFromPhpError();
    }
}
function inet_ntop(string $ip) : string
{
    \error_clear_last();
    $result = \inet_ntop($ip);
    if ($result === \false) {
        throw NetworkException::createFromPhpError();
    }
    return $result;
}
function long2ip(int $ip) : string
{
    \error_clear_last();
    $result = \long2ip($ip);
    if ($result === \false) {
        throw NetworkException::createFromPhpError();
    }
    return $result;
}
function net_get_interfaces() : array
{
    \error_clear_last();
    $result = \net_get_interfaces();
    if ($result === \false) {
        throw NetworkException::createFromPhpError();
    }
    return $result;
}
function openlog(string $prefix, int $flags, int $facility) : void
{
    \error_clear_last();
    $result = \openlog($prefix, $flags, $facility);
    if ($result === \false) {
        throw NetworkException::createFromPhpError();
    }
}
function pfsockopen(string $hostname, int $port = -1, ?int &$error_code = null, ?string &$error_message = null, float $timeout = null)
{
    \error_clear_last();
    if ($timeout !== null) {
        $result = \pfsockopen($hostname, $port, $error_code, $error_message, $timeout);
    } else {
        $result = \pfsockopen($hostname, $port, $error_code, $error_message);
    }
    if ($result === \false) {
        throw NetworkException::createFromPhpError();
    }
    return $result;
}
function syslog(int $priority, string $message) : void
{
    \error_clear_last();
    $result = \syslog($priority, $message);
    if ($result === \false) {
        throw NetworkException::createFromPhpError();
    }
}
