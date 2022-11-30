<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\PcntlException;
function pcntl_getpriority(int $process_id = null, int $mode = \PRIO_PROCESS) : int
{
    \error_clear_last();
    if ($mode !== \PRIO_PROCESS) {
        $result = \pcntl_getpriority($process_id, $mode);
    } elseif ($process_id !== null) {
        $result = \pcntl_getpriority($process_id);
    } else {
        $result = \pcntl_getpriority();
    }
    if ($result === \false) {
        throw PcntlException::createFromPhpError();
    }
    return $result;
}
function pcntl_setpriority(int $priority, int $process_id = null, int $mode = \PRIO_PROCESS) : void
{
    \error_clear_last();
    if ($mode !== \PRIO_PROCESS) {
        $result = \pcntl_setpriority($priority, $process_id, $mode);
    } elseif ($process_id !== null) {
        $result = \pcntl_setpriority($priority, $process_id);
    } else {
        $result = \pcntl_setpriority($priority);
    }
    if ($result === \false) {
        throw PcntlException::createFromPhpError();
    }
}
function pcntl_signal_dispatch() : void
{
    \error_clear_last();
    $result = \pcntl_signal_dispatch();
    if ($result === \false) {
        throw PcntlException::createFromPhpError();
    }
}
function pcntl_signal(int $signal, $handler, bool $restart_syscalls = \true) : void
{
    \error_clear_last();
    $result = \pcntl_signal($signal, $handler, $restart_syscalls);
    if ($result === \false) {
        throw PcntlException::createFromPhpError();
    }
}
function pcntl_sigprocmask(int $mode, array $signals, ?array &$old_signals = null) : void
{
    \error_clear_last();
    $result = \pcntl_sigprocmask($mode, $signals, $old_signals);
    if ($result === \false) {
        throw PcntlException::createFromPhpError();
    }
}
function pcntl_sigtimedwait(array $signals, ?array &$info = [], int $seconds = 0, int $nanoseconds = 0) : int
{
    \error_clear_last();
    $result = \pcntl_sigtimedwait($signals, $info, $seconds, $nanoseconds);
    if ($result === \false) {
        throw PcntlException::createFromPhpError();
    }
    return $result;
}
function pcntl_sigwaitinfo(array $signals, ?array &$info = []) : int
{
    \error_clear_last();
    $result = \pcntl_sigwaitinfo($signals, $info);
    if ($result === \false) {
        throw PcntlException::createFromPhpError();
    }
    return $result;
}
