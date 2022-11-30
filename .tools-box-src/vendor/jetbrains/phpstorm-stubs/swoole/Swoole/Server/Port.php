<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Swoole\Server;

class Port
{
    public $host;
    public $port = 0;
    public $type = 0;
    public $sock = -1;
    public $setting;
    public $connections;
    private $onConnect;
    private $onReceive;
    private $onClose;
    private $onPacket;
    private $onBufferFull;
    private $onBufferEmpty;
    private $onRequest;
    private $onHandShake;
    private $onOpen;
    private $onMessage;
    private $onDisconnect;
    private function __construct()
    {
    }
    public function __destruct()
    {
    }
    public function set(array $settings)
    {
    }
    public function on($event_name, callable $callback)
    {
    }
    public function getCallback($event_name)
    {
    }
    public function getSocket()
    {
    }
}
