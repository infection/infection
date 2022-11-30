<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Swoole\Coroutine\Http;

class Server
{
    public $fd = -1;
    public $host;
    public $port = -1;
    public $ssl = \false;
    public $settings;
    public $errCode = 0;
    public $errMsg = '';
    public function __construct($host, $port = null, $ssl = null, $reuse_port = null)
    {
    }
    public function __destruct()
    {
    }
    public function set(array $settings)
    {
    }
    public function handle($pattern, callable $callback)
    {
    }
    public function start()
    {
    }
    public function shutdown()
    {
    }
    private function onAccept()
    {
    }
}
