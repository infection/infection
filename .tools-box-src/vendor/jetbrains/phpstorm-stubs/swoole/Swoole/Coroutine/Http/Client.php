<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Swoole\Coroutine\Http;

class Client
{
    public $errCode = 0;
    public $errMsg = '';
    public $connected = \false;
    public $host = '';
    public $port = 0;
    public $ssl = \false;
    public $setting;
    public $requestMethod;
    public $requestHeaders;
    public $requestBody;
    public $uploadFiles;
    public $downloadFile;
    public $downloadOffset = 0;
    public $statusCode = 0;
    public $headers;
    public $set_cookie_headers;
    public $cookies;
    public $body = '';
    public function __construct($host, $port = null, $ssl = null)
    {
    }
    public function __destruct()
    {
    }
    public function set(array $settings)
    {
    }
    public function getDefer()
    {
    }
    public function setDefer($defer = null)
    {
    }
    public function setMethod($method)
    {
    }
    public function setHeaders(array $headers)
    {
    }
    public function setBasicAuth($username, $password)
    {
    }
    public function setCookies(array $cookies)
    {
    }
    public function setData($data)
    {
    }
    public function addFile($path, $name, $type = null, $filename = null, $offset = null, $length = null)
    {
    }
    public function addData($path, $name, $type = null, $filename = null)
    {
    }
    public function execute($path)
    {
    }
    public function getpeername()
    {
    }
    public function getsockname()
    {
    }
    public function get($path)
    {
    }
    public function post($path, $data)
    {
    }
    public function download($path, $file, $offset = null)
    {
    }
    public function getBody()
    {
    }
    public function getHeaders()
    {
    }
    public function getCookies()
    {
    }
    public function getStatusCode()
    {
    }
    public function getHeaderOut()
    {
    }
    public function getPeerCert()
    {
    }
    public function upgrade($path)
    {
    }
    public function push($data, $opcode = null, $flags = null)
    {
    }
    public function recv($timeout = null)
    {
    }
    public function close()
    {
    }
}
