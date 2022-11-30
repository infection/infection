<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Swoole;

class Coroutine
{
    public static function create(callable $func, ...$params)
    {
    }
    public static function defer($callback)
    {
    }
    public static function set(array $options)
    {
    }
    public static function getOptions()
    {
    }
    public static function exists($cid)
    {
    }
    public static function yield()
    {
    }
    public static function cancel($cid)
    {
    }
    public static function join($cid_array, $timeout = -1)
    {
    }
    public static function isCanceled()
    {
    }
    public static function suspend()
    {
    }
    public static function resume($cid)
    {
    }
    public static function stats()
    {
    }
    public static function getCid()
    {
    }
    public static function getuid()
    {
    }
    public static function getPcid($cid = null)
    {
    }
    public static function getContext($cid = null)
    {
    }
    public static function getBackTrace($cid = null, $options = null, $limit = null)
    {
    }
    public static function printBackTrace($cid = null, $options = null, $limit = null)
    {
    }
    public static function getElapsed($cid = null)
    {
    }
    public static function getStackUsage(int $cid = null)
    {
    }
    public static function list()
    {
    }
    public static function listCoroutines()
    {
    }
    public static function enableScheduler()
    {
    }
    public static function disableScheduler()
    {
    }
    public static function gethostbyname($domain_name, $family = null, $timeout = null)
    {
    }
    public static function dnsLookup($domain_name, $timeout = null, $type = null)
    {
    }
    public static function exec($command, $get_error_stream = null)
    {
    }
    public static function sleep($seconds)
    {
    }
    public static function getaddrinfo($hostname, $family = null, $socktype = null, $protocol = null, $service = null, $timeout = null)
    {
    }
    public static function statvfs($path)
    {
    }
    public static function readFile($filename)
    {
    }
    public static function writeFile($filename, $data, $flags = null)
    {
    }
    public static function wait($timeout = null)
    {
    }
    public static function waitPid($pid, $timeout = null)
    {
    }
    public static function waitSignal($signo, $timeout = null)
    {
    }
    public static function waitEvent($fd, $events = null, $timeout = null)
    {
    }
    public static function fread($handle, $length = null)
    {
    }
    public static function fgets($handle)
    {
    }
    public static function fwrite($handle, $string, $length = null)
    {
    }
}
