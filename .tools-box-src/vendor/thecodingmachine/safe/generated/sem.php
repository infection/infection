<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\SemException;
function msg_get_queue(int $key, int $permissions = 0666)
{
    \error_clear_last();
    $safeResult = \msg_get_queue($key, $permissions);
    if ($safeResult === \false) {
        throw SemException::createFromPhpError();
    }
    return $safeResult;
}
function msg_queue_exists(int $key) : void
{
    \error_clear_last();
    $safeResult = \msg_queue_exists($key);
    if ($safeResult === \false) {
        throw SemException::createFromPhpError();
    }
}
function msg_receive($queue, int $desired_message_type, ?int &$received_message_type, int $max_message_size, &$message, bool $unserialize = \true, int $flags = 0, ?int &$error_code = null) : void
{
    \error_clear_last();
    $safeResult = \msg_receive($queue, $desired_message_type, $received_message_type, $max_message_size, $message, $unserialize, $flags, $error_code);
    if ($safeResult === \false) {
        throw SemException::createFromPhpError();
    }
}
function msg_remove_queue($queue) : void
{
    \error_clear_last();
    $safeResult = \msg_remove_queue($queue);
    if ($safeResult === \false) {
        throw SemException::createFromPhpError();
    }
}
function msg_send($queue, int $message_type, $message, bool $serialize = \true, bool $blocking = \true, ?int &$error_code = null) : void
{
    \error_clear_last();
    $safeResult = \msg_send($queue, $message_type, $message, $serialize, $blocking, $error_code);
    if ($safeResult === \false) {
        throw SemException::createFromPhpError();
    }
}
function msg_set_queue($queue, array $data) : void
{
    \error_clear_last();
    $safeResult = \msg_set_queue($queue, $data);
    if ($safeResult === \false) {
        throw SemException::createFromPhpError();
    }
}
function msg_stat_queue($queue) : array
{
    \error_clear_last();
    $safeResult = \msg_stat_queue($queue);
    if ($safeResult === \false) {
        throw SemException::createFromPhpError();
    }
    return $safeResult;
}
function sem_acquire($semaphore, bool $non_blocking = \false) : void
{
    \error_clear_last();
    $safeResult = \sem_acquire($semaphore, $non_blocking);
    if ($safeResult === \false) {
        throw SemException::createFromPhpError();
    }
}
function sem_get(int $key, int $max_acquire = 1, int $permissions = 0666, bool $auto_release = \true)
{
    \error_clear_last();
    $safeResult = \sem_get($key, $max_acquire, $permissions, $auto_release);
    if ($safeResult === \false) {
        throw SemException::createFromPhpError();
    }
    return $safeResult;
}
function sem_release($semaphore) : void
{
    \error_clear_last();
    $safeResult = \sem_release($semaphore);
    if ($safeResult === \false) {
        throw SemException::createFromPhpError();
    }
}
function sem_remove($semaphore) : void
{
    \error_clear_last();
    $safeResult = \sem_remove($semaphore);
    if ($safeResult === \false) {
        throw SemException::createFromPhpError();
    }
}
function shm_attach(int $key, int $size = null, int $permissions = 0666)
{
    \error_clear_last();
    if ($permissions !== 0666) {
        $safeResult = \shm_attach($key, $size, $permissions);
    } elseif ($size !== null) {
        $safeResult = \shm_attach($key, $size);
    } else {
        $safeResult = \shm_attach($key);
    }
    if ($safeResult === \false) {
        throw SemException::createFromPhpError();
    }
    return $safeResult;
}
function shm_detach($shm) : void
{
    \error_clear_last();
    $safeResult = \shm_detach($shm);
    if ($safeResult === \false) {
        throw SemException::createFromPhpError();
    }
}
function shm_put_var($shm, int $key, $value) : void
{
    \error_clear_last();
    $safeResult = \shm_put_var($shm, $key, $value);
    if ($safeResult === \false) {
        throw SemException::createFromPhpError();
    }
}
function shm_remove_var($shm, int $key) : void
{
    \error_clear_last();
    $safeResult = \shm_remove_var($shm, $key);
    if ($safeResult === \false) {
        throw SemException::createFromPhpError();
    }
}
function shm_remove($shm) : void
{
    \error_clear_last();
    $safeResult = \shm_remove($shm);
    if ($safeResult === \false) {
        throw SemException::createFromPhpError();
    }
}
