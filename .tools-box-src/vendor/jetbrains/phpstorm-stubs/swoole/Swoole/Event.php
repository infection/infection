<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Swoole;

class Event
{
    public static function add($fd, ?callable $read_callback, ?callable $write_callback = null, $events = null)
    {
    }
    public static function del($fd)
    {
    }
    public static function set($fd, ?callable $read_callback = null, ?callable $write_callback = null, $events = null)
    {
    }
    public static function isset($fd, $events = null)
    {
    }
    public static function dispatch()
    {
    }
    public static function defer(callable $callback)
    {
    }
    public static function cycle(?callable $callback, $before = null)
    {
    }
    public static function write($fd, $data)
    {
    }
    public static function wait()
    {
    }
    public static function rshutdown()
    {
    }
    public static function exit()
    {
    }
}
