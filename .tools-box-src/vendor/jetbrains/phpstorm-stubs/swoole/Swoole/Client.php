<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Swoole;

class Client
{
    public const MSG_OOB = 1;
    public const MSG_PEEK = 2;
    public const MSG_DONTWAIT = 64;
    public const MSG_WAITALL = 256;
    public const SHUT_RDWR = 2;
    public const SHUT_RD = 0;
    public const SHUT_WR = 1;
    public $errCode = 0;
    public $sock = -1;
    public $reuse = \false;
    public $reuseCount = 0;
    public $type = 0;
    public $id;
    public $setting;
    public function __construct($type, $async = null, $id = null)
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
    public function recv($size = null, $flag = null)
    {
    }
    public function send($data, $flag = null)
    {
    }
    public function sendfile($filename, $offset = null, $length = null)
    {
    }
    public function sendto($ip, $port, $data)
    {
    }
    public function shutdown($how)
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
    public function close($force = null)
    {
    }
    public function getSocket()
    {
    }
}
