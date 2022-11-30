<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Swoole\WebSocket;

class CloseFrame extends Frame
{
    public $opcode = 8;
    public $code = 1000;
    public $reason = '';
}
