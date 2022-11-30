<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\LibeventException;
function event_add($event, int $timeout = -1) : void
{
    \error_clear_last();
    $result = \event_add($event, $timeout);
    if ($result === \false) {
        throw LibeventException::createFromPhpError();
    }
}
function event_base_loopbreak($event_base) : void
{
    \error_clear_last();
    $result = \event_base_loopbreak($event_base);
    if ($result === \false) {
        throw LibeventException::createFromPhpError();
    }
}
function event_base_loopexit($event_base, int $timeout = -1) : void
{
    \error_clear_last();
    $result = \event_base_loopexit($event_base, $timeout);
    if ($result === \false) {
        throw LibeventException::createFromPhpError();
    }
}
function event_base_new()
{
    \error_clear_last();
    $result = \event_base_new();
    if ($result === \false) {
        throw LibeventException::createFromPhpError();
    }
    return $result;
}
function event_base_priority_init($event_base, int $npriorities) : void
{
    \error_clear_last();
    $result = \event_base_priority_init($event_base, $npriorities);
    if ($result === \false) {
        throw LibeventException::createFromPhpError();
    }
}
function event_base_reinit($event_base) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\event_base_reinit($event_base);
    if ($result === \false) {
        throw LibeventException::createFromPhpError();
    }
}
function event_base_set($event, $event_base) : void
{
    \error_clear_last();
    $result = \event_base_set($event, $event_base);
    if ($result === \false) {
        throw LibeventException::createFromPhpError();
    }
}
function event_buffer_base_set($bevent, $event_base) : void
{
    \error_clear_last();
    $result = \event_buffer_base_set($bevent, $event_base);
    if ($result === \false) {
        throw LibeventException::createFromPhpError();
    }
}
function event_buffer_disable($bevent, int $events) : void
{
    \error_clear_last();
    $result = \event_buffer_disable($bevent, $events);
    if ($result === \false) {
        throw LibeventException::createFromPhpError();
    }
}
function event_buffer_enable($bevent, int $events) : void
{
    \error_clear_last();
    $result = \event_buffer_enable($bevent, $events);
    if ($result === \false) {
        throw LibeventException::createFromPhpError();
    }
}
function event_buffer_new($stream, $readcb, $writecb, $errorcb, $arg = null)
{
    \error_clear_last();
    if ($arg !== null) {
        $result = \event_buffer_new($stream, $readcb, $writecb, $errorcb, $arg);
    } else {
        $result = \event_buffer_new($stream, $readcb, $writecb, $errorcb);
    }
    if ($result === \false) {
        throw LibeventException::createFromPhpError();
    }
    return $result;
}
function event_buffer_priority_set($bevent, int $priority) : void
{
    \error_clear_last();
    $result = \event_buffer_priority_set($bevent, $priority);
    if ($result === \false) {
        throw LibeventException::createFromPhpError();
    }
}
function event_buffer_set_callback($event, $readcb, $writecb, $errorcb, $arg = null) : void
{
    \error_clear_last();
    if ($arg !== null) {
        $result = \event_buffer_set_callback($event, $readcb, $writecb, $errorcb, $arg);
    } else {
        $result = \event_buffer_set_callback($event, $readcb, $writecb, $errorcb);
    }
    if ($result === \false) {
        throw LibeventException::createFromPhpError();
    }
}
function event_buffer_write($bevent, string $data, int $data_size = -1) : void
{
    \error_clear_last();
    $result = \event_buffer_write($bevent, $data, $data_size);
    if ($result === \false) {
        throw LibeventException::createFromPhpError();
    }
}
function event_del($event) : void
{
    \error_clear_last();
    $result = \event_del($event);
    if ($result === \false) {
        throw LibeventException::createFromPhpError();
    }
}
function event_new()
{
    \error_clear_last();
    $result = \event_new();
    if ($result === \false) {
        throw LibeventException::createFromPhpError();
    }
    return $result;
}
function event_priority_set($event, int $priority) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\event_priority_set($event, $priority);
    if ($result === \false) {
        throw LibeventException::createFromPhpError();
    }
}
function event_set($event, $fd, int $events, $callback, $arg = null) : void
{
    \error_clear_last();
    if ($arg !== null) {
        $result = \event_set($event, $fd, $events, $callback, $arg);
    } else {
        $result = \event_set($event, $fd, $events, $callback);
    }
    if ($result === \false) {
        throw LibeventException::createFromPhpError();
    }
}
function event_timer_set($event, callable $callback, $arg = null) : void
{
    \error_clear_last();
    if ($arg !== null) {
        $result = \event_timer_set($event, $callback, $arg);
    } else {
        $result = \event_timer_set($event, $callback);
    }
    if ($result === \false) {
        throw LibeventException::createFromPhpError();
    }
}
