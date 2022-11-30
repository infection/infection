<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Swoole\Http;

class Response
{
    public $fd = 0;
    public $socket;
    public $header;
    public $cookie;
    public $trailer;
    public function __destruct()
    {
    }
    public function initHeader()
    {
    }
    public function isWritable()
    {
    }
    public function cookie($name, $value = null, $expires = null, $path = null, $domain = null, $secure = null, $httponly = null, $samesite = null, $priority = null)
    {
    }
    public function setCookie($name, $value = null, $expires = null, $path = null, $domain = null, $secure = null, $httponly = null, $samesite = null, $priority = null)
    {
    }
    public function rawcookie($name, $value = null, $expires = null, $path = null, $domain = null, $secure = null, $httponly = null, $samesite = null, $priority = null)
    {
    }
    public function status($http_code, $reason = null)
    {
    }
    public function setStatusCode($http_code, $reason = null)
    {
    }
    public function header($key, $value, $format = null)
    {
    }
    public function setHeader($key, $value, $format = null)
    {
    }
    public function trailer($key, $value)
    {
    }
    public function ping()
    {
    }
    public function goaway()
    {
    }
    public function write($content)
    {
    }
    public function end($content = null)
    {
    }
    public function sendfile($filename, $offset = null, $length = null)
    {
    }
    public function redirect($location, $http_code = null)
    {
    }
    public function detach()
    {
    }
    public static function create($server, $fd = null)
    {
    }
    public function upgrade()
    {
    }
    public function push($data, $opcode = null, $flags = null)
    {
    }
    public function recv()
    {
    }
    public function close()
    {
    }
}
