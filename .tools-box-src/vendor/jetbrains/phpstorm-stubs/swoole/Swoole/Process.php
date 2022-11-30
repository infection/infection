<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Swoole;

class Process
{
    public const IPC_NOWAIT = 256;
    public const PIPE_MASTER = 1;
    public const PIPE_WORKER = 2;
    public const PIPE_READ = 3;
    public const PIPE_WRITE = 4;
    public $pipe;
    public $msgQueueId;
    public $msgQueueKey;
    public $pid;
    public $id;
    private $callback;
    public function __construct(callable $callback, $redirect_stdin_and_stdout = null, $pipe_type = null, $enable_coroutine = null)
    {
    }
    public function __destruct()
    {
    }
    public static function wait($blocking = null)
    {
    }
    public static function signal($signal_no, $callback)
    {
    }
    public static function alarm($usec, $type = null)
    {
    }
    public static function kill($pid, $signal_no = null)
    {
    }
    public static function daemon($nochdir = null, $noclose = null, $pipes = null)
    {
    }
    public function setPriority($which, $priority)
    {
    }
    public function getPriority($which)
    {
    }
    public function set(array $settings)
    {
    }
    public function setTimeout($seconds)
    {
    }
    public function setBlocking($blocking)
    {
    }
    public function useQueue($key = null, $mode = null, $capacity = null)
    {
    }
    public function statQueue()
    {
    }
    public function freeQueue()
    {
    }
    public function start()
    {
    }
    public function write($data)
    {
    }
    public function close()
    {
    }
    public function read($size = null)
    {
    }
    public function push($data)
    {
    }
    public function pop($size = null)
    {
    }
    public function exit($exit_code = null)
    {
    }
    public function exec($exec_file, $args)
    {
    }
    public function exportSocket()
    {
    }
    public function name($process_name)
    {
    }
}
