<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Swoole\Coroutine;

class MySQL
{
    public $serverInfo;
    public $sock = -1;
    public $connected = \false;
    public $connect_errno = 0;
    public $connect_error = '';
    public $affected_rows = 0;
    public $insert_id = 0;
    public $error = '';
    public $errno = 0;
    public function __construct()
    {
    }
    public function __destruct()
    {
    }
    public function getDefer()
    {
    }
    public function setDefer($defer = null)
    {
    }
    public function connect(array $server_config = null)
    {
    }
    public function query($sql, $timeout = null)
    {
    }
    public function fetch()
    {
    }
    public function fetchAll()
    {
    }
    public function nextResult()
    {
    }
    public function prepare($query, $timeout = null)
    {
    }
    public function recv()
    {
    }
    public function begin($timeout = null)
    {
    }
    public function commit($timeout = null)
    {
    }
    public function rollback($timeout = null)
    {
    }
    public function escape($string, $flags = null)
    {
    }
    public function close()
    {
    }
}
