<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Swoole\Server;

class Task
{
    public $data;
    public $dispatch_time = 0;
    public $id = -1;
    public $worker_id = -1;
    public $flags = 0;
    public function finish($data)
    {
    }
    public static function pack($data)
    {
    }
}
