<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Swoole;

class Server
{
    public $setting;
    public $connections;
    public $host = '';
    public $port = 0;
    public $type = 0;
    public $mode = 0;
    public $ports;
    public $master_pid = 0;
    public $manager_pid = 0;
    public $worker_id = -1;
    public $taskworker = \false;
    public $worker_pid = 0;
    public $stats_timer;
    public $admin_server;
    private $onStart;
    private $onBeforeShutdown;
    private $onShutdown;
    private $onWorkerStart;
    private $onWorkerStop;
    private $onBeforeReload;
    private $onAfterReload;
    private $onWorkerExit;
    private $onWorkerError;
    private $onTask;
    private $onFinish;
    private $onManagerStart;
    private $onManagerStop;
    private $onPipeMessage;
    public function __construct($host, $port = null, $mode = null, $sock_type = null)
    {
    }
    public function __destruct()
    {
    }
    public function listen($host, $port, $sock_type)
    {
    }
    public function addlistener($host, $port, $sock_type)
    {
    }
    public function on($event_name, callable $callback)
    {
    }
    public function getCallback($event_name)
    {
    }
    public function set(array $settings)
    {
    }
    public function start()
    {
    }
    public function send($fd, $send_data, $server_socket = null)
    {
    }
    public function sendto($ip, $port, $send_data, $server_socket = null)
    {
    }
    public function sendwait($conn_fd, $send_data)
    {
    }
    public function exists($fd)
    {
    }
    public function exist($fd)
    {
    }
    public function protect($fd, $is_protected = null)
    {
    }
    public function sendfile($conn_fd, $filename, $offset = null, $length = null)
    {
    }
    public function close($fd, $reset = null)
    {
    }
    public function confirm($fd)
    {
    }
    public function pause($fd)
    {
    }
    public function resume($fd)
    {
    }
    public function task($data, $worker_id = null, ?callable $finish_callback = null)
    {
    }
    public function taskwait($data, $timeout = null, $worker_id = null)
    {
    }
    public function taskWaitMulti(array $tasks, $timeout = null)
    {
    }
    public function taskCo(array $tasks, $timeout = null)
    {
    }
    public function finish($data)
    {
    }
    public function reload()
    {
    }
    public function shutdown()
    {
    }
    public function stop($worker_id = null)
    {
    }
    public function getLastError()
    {
    }
    public function heartbeat($reactor_id)
    {
    }
    public function getClientInfo($fd, $reactor_id = null)
    {
    }
    public function getClientList($start_fd, $find_count = null)
    {
    }
    public function getWorkerId()
    {
    }
    public function getWorkerPid(int $worker_id = -1)
    {
    }
    public function getWorkerStatus($worker_id = null)
    {
    }
    public function command(string $name, int $process_id, int $process_type, $data, bool $json_decode = \true)
    {
    }
    public function addCommand(string $name, int $accepted_process_types, callable $callback)
    {
    }
    public function getManagerPid()
    {
    }
    public function getMasterPid()
    {
    }
    public function connection_info($fd, $reactor_id = null)
    {
    }
    public function connection_list($start_fd, $find_count = null)
    {
    }
    public function sendMessage($message, $dst_worker_id)
    {
    }
    public function addProcess(Process $process)
    {
    }
    public function stats()
    {
    }
    public function getSocket($port = null)
    {
    }
    public function bind($fd, $uid)
    {
    }
    public function after(int $ms, callable $callback, ...$params)
    {
    }
    public function tick(int $ms, callable $callback, ...$params)
    {
    }
    public function clearTimer(int $timer_id)
    {
    }
    public function defer(callable $callback)
    {
    }
}
