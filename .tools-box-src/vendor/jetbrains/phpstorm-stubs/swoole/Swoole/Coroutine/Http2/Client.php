<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Swoole\Coroutine\Http2;

class Client
{
    public $errCode = 0;
    public $errMsg = 0;
    public $sock = -1;
    public $type = 0;
    public $setting;
    public $connected = \false;
    public $host;
    public $port = 0;
    public $ssl = \false;
    public function __construct($host, $port = null, $open_ssl = null)
    {
    }
    public function __destruct()
    {
    }
    public function set(array $settings)
    {
    }
    public function connect()
    {
    }
    public function stats($key = null)
    {
    }
    public function isStreamExist($stream_id)
    {
    }
    public function send($request)
    {
    }
    public function write($stream_id, $data, $end_stream = null)
    {
    }
    public function recv($timeout = null)
    {
    }
    public function read($timeout = null)
    {
    }
    public function goaway($error_code = null, $debug_data = null)
    {
    }
    public function ping()
    {
    }
    public function close()
    {
    }
}
