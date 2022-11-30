<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Swoole\WebSocket;

class Frame
{
    public $fd = 0;
    public $data = '';
    public $opcode = 1;
    public $flags = 1;
    public $finish;
    public function __toString() : string
    {
    }
    public static function pack($data, $opcode = null, $flags = null)
    {
    }
    public static function unpack($data)
    {
    }
}
