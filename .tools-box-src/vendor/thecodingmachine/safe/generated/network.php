<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\NetworkException;
function closelog() : void
{
    \error_clear_last();
    $safeResult = \closelog();
    if ($safeResult === \false) {
        throw NetworkException::createFromPhpError();
    }
}
function dns_get_record(string $hostname, int $type = \DNS_ANY, ?array &$authoritative_name_servers = null, ?array &$additional_records = null, bool $raw = \false) : array
{
    \error_clear_last();
    $safeResult = \dns_get_record($hostname, $type, $authoritative_name_servers, $additional_records, $raw);
    if ($safeResult === \false) {
        throw NetworkException::createFromPhpError();
    }
    return $safeResult;
}
function fsockopen(string $hostname, int $port = -1, ?int &$error_code = null, ?string &$error_message = null, float $timeout = null)
{
    \error_clear_last();
    if ($timeout !== null) {
        $safeResult = \fsockopen($hostname, $port, $error_code, $error_message, $timeout);
    } else {
        $safeResult = \fsockopen($hostname, $port, $error_code, $error_message);
    }
    if ($safeResult === \false) {
        throw NetworkException::createFromPhpError();
    }
    return $safeResult;
}
function gethostname() : string
{
    \error_clear_last();
    $safeResult = \gethostname();
    if ($safeResult === \false) {
        throw NetworkException::createFromPhpError();
    }
    return $safeResult;
}
function getprotobyname(string $protocol) : int
{
    \error_clear_last();
    $safeResult = \getprotobyname($protocol);
    if ($safeResult === \false) {
        throw NetworkException::createFromPhpError();
    }
    return $safeResult;
}
function getprotobynumber(int $protocol) : string
{
    \error_clear_last();
    $safeResult = \getprotobynumber($protocol);
    if ($safeResult === \false) {
        throw NetworkException::createFromPhpError();
    }
    return $safeResult;
}
function getservbyport(int $port, string $protocol) : string
{
    \error_clear_last();
    $safeResult = \getservbyport($port, $protocol);
    if ($safeResult === \false) {
        throw NetworkException::createFromPhpError();
    }
    return $safeResult;
}
function header_register_callback(callable $callback) : void
{
    \error_clear_last();
    $safeResult = \header_register_callback($callback);
    if ($safeResult === \false) {
        throw NetworkException::createFromPhpError();
    }
}
function inet_ntop(string $ip) : string
{
    \error_clear_last();
    $safeResult = \inet_ntop($ip);
    if ($safeResult === \false) {
        throw NetworkException::createFromPhpError();
    }
    return $safeResult;
}
function long2ip(int $ip) : string
{
    \error_clear_last();
    $safeResult = \long2ip($ip);
    if ($safeResult === \false) {
        throw NetworkException::createFromPhpError();
    }
    return $safeResult;
}
function net_get_interfaces() : array
{
    \error_clear_last();
    $safeResult = \net_get_interfaces();
    if ($safeResult === \false) {
        throw NetworkException::createFromPhpError();
    }
    return $safeResult;
}
function openlog(string $prefix, int $flags, int $facility) : void
{
    \error_clear_last();
    $safeResult = \openlog($prefix, $flags, $facility);
    if ($safeResult === \false) {
        throw NetworkException::createFromPhpError();
    }
}
function pfsockopen(string $hostname, int $port = -1, ?int &$error_code = null, ?string &$error_message = null, float $timeout = null)
{
    \error_clear_last();
    if ($timeout !== null) {
        $safeResult = \pfsockopen($hostname, $port, $error_code, $error_message, $timeout);
    } else {
        $safeResult = \pfsockopen($hostname, $port, $error_code, $error_message);
    }
    if ($safeResult === \false) {
        throw NetworkException::createFromPhpError();
    }
    return $safeResult;
}
function syslog(int $priority, string $message) : void
{
    \error_clear_last();
    $safeResult = \syslog($priority, $message);
    if ($safeResult === \false) {
        throw NetworkException::createFromPhpError();
    }
}
