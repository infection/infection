<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\PosixException;
function posix_access(string $filename, int $flags = 0) : void
{
    \error_clear_last();
    $result = \posix_access($filename, $flags);
    if ($result === \false) {
        throw PosixException::createFromPhpError();
    }
}
function posix_getgrgid(int $group_id) : array
{
    \error_clear_last();
    $result = \posix_getgrgid($group_id);
    if ($result === \false) {
        throw PosixException::createFromPhpError();
    }
    return $result;
}
function posix_getgrnam(string $name) : array
{
    \error_clear_last();
    $result = \posix_getgrnam($name);
    if ($result === \false) {
        throw PosixException::createFromPhpError();
    }
    return $result;
}
function posix_getgroups() : array
{
    \error_clear_last();
    $result = \posix_getgroups();
    if ($result === \false) {
        throw PosixException::createFromPhpError();
    }
    return $result;
}
function posix_getlogin() : string
{
    \error_clear_last();
    $result = \posix_getlogin();
    if ($result === \false) {
        throw PosixException::createFromPhpError();
    }
    return $result;
}
function posix_getpwuid(int $user_id) : array
{
    \error_clear_last();
    $result = \posix_getpwuid($user_id);
    if ($result === \false) {
        throw PosixException::createFromPhpError();
    }
    return $result;
}
function posix_getrlimit() : array
{
    \error_clear_last();
    $result = \posix_getrlimit();
    if ($result === \false) {
        throw PosixException::createFromPhpError();
    }
    return $result;
}
function posix_getsid(int $process_id) : int
{
    \error_clear_last();
    $result = \posix_getsid($process_id);
    if ($result === \false) {
        throw PosixException::createFromPhpError();
    }
    return $result;
}
function posix_initgroups(string $username, int $group_id) : void
{
    \error_clear_last();
    $result = \posix_initgroups($username, $group_id);
    if ($result === \false) {
        throw PosixException::createFromPhpError();
    }
}
function posix_kill(int $process_id, int $signal) : void
{
    \error_clear_last();
    $result = \posix_kill($process_id, $signal);
    if ($result === \false) {
        throw PosixException::createFromPhpError();
    }
}
function posix_mkfifo(string $filename, int $permissions) : void
{
    \error_clear_last();
    $result = \posix_mkfifo($filename, $permissions);
    if ($result === \false) {
        throw PosixException::createFromPhpError();
    }
}
function posix_mknod(string $filename, int $flags, int $major = 0, int $minor = 0) : void
{
    \error_clear_last();
    $result = \posix_mknod($filename, $flags, $major, $minor);
    if ($result === \false) {
        throw PosixException::createFromPhpError();
    }
}
function posix_setegid(int $group_id) : void
{
    \error_clear_last();
    $result = \posix_setegid($group_id);
    if ($result === \false) {
        throw PosixException::createFromPhpError();
    }
}
function posix_seteuid(int $user_id) : void
{
    \error_clear_last();
    $result = \posix_seteuid($user_id);
    if ($result === \false) {
        throw PosixException::createFromPhpError();
    }
}
function posix_setgid(int $group_id) : void
{
    \error_clear_last();
    $result = \posix_setgid($group_id);
    if ($result === \false) {
        throw PosixException::createFromPhpError();
    }
}
function posix_setpgid(int $process_id, int $process_group_id) : void
{
    \error_clear_last();
    $result = \posix_setpgid($process_id, $process_group_id);
    if ($result === \false) {
        throw PosixException::createFromPhpError();
    }
}
function posix_setrlimit(int $resource, int $soft_limit, int $hard_limit) : void
{
    \error_clear_last();
    $result = \posix_setrlimit($resource, $soft_limit, $hard_limit);
    if ($result === \false) {
        throw PosixException::createFromPhpError();
    }
}
function posix_setuid(int $user_id) : void
{
    \error_clear_last();
    $result = \posix_setuid($user_id);
    if ($result === \false) {
        throw PosixException::createFromPhpError();
    }
}
function posix_times() : array
{
    \error_clear_last();
    $result = \posix_times();
    if ($result === \false) {
        throw PosixException::createFromPhpError();
    }
    return $result;
}
function posix_uname() : array
{
    \error_clear_last();
    $result = \posix_uname();
    if ($result === \false) {
        throw PosixException::createFromPhpError();
    }
    return $result;
}
