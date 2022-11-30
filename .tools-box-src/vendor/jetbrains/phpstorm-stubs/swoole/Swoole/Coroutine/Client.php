<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Swoole\Coroutine;

class Client
{
    public const MSG_OOB = 1;
    public const MSG_PEEK = 2;
    public const MSG_DONTWAIT = 64;
    public const MSG_WAITALL = 256;
    public $errCode = 0;
    public $errMsg = '';
    public $fd = -1;
    public $type = 1;
    public $setting;
    public $connected = \false;
    private $socket;
    public function __construct($type)
    {
    }
    public function __destruct()
    {
    }
    public function set(array $settings)
    {
    }
    public function connect($host, $port = null, $timeout = null, $sock_flag = null)
    {
    }
    public function recv($timeout = null)
    {
    }
    public function peek($length = null)
    {
    }
    public function send($data)
    {
    }
    public function sendfile($filename, $offset = null, $length = null)
    {
    }
    public function sendto($address, $port, $data)
    {
    }
    public function recvfrom($length, &$address, &$port = null)
    {
    }
    public function enableSSL()
    {
    }
    public function getPeerCert()
    {
    }
    public function verifyPeerCert()
    {
    }
    public function isConnected()
    {
    }
    public function getsockname()
    {
    }
    public function getpeername()
    {
    }
    public function close()
    {
    }
    public function exportSocket()
    {
    }
}
