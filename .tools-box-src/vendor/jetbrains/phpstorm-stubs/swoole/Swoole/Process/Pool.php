<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Swoole\Process;

class Pool
{
    public $master_pid = -1;
    public $workers;
    public function __construct($worker_num, $ipc_type = null, $msgqueue_key = null, $enable_coroutine = null)
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
    public function getProcess($worker_id = null)
    {
    }
    public function listen($host, $port = null, $backlog = null)
    {
    }
    public function write($data)
    {
    }
    public function detach()
    {
    }
    public function start()
    {
    }
    public function stop()
    {
    }
    public function shutdown()
    {
    }
}
