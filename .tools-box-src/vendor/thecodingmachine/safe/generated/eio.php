<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\EioException;
function eio_busy(int $delay, int $pri = \EIO_PRI_DEFAULT, callable $callback = null, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_busy($delay, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_chmod(string $path, int $mode, int $pri = \EIO_PRI_DEFAULT, callable $callback = null, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_chmod($path, $mode, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_chown(string $path, int $uid, int $gid = -1, int $pri = \EIO_PRI_DEFAULT, callable $callback = null, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_chown($path, $uid, $gid, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_close($fd, int $pri = \EIO_PRI_DEFAULT, callable $callback = null, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_close($fd, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_custom(callable $execute, int $pri, callable $callback, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_custom($execute, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_dup2($fd, $fd2, int $pri = \EIO_PRI_DEFAULT, callable $callback = null, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_dup2($fd, $fd2, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_event_loop() : void
{
    \error_clear_last();
    $safeResult = \eio_event_loop();
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
}
function eio_fallocate($fd, int $mode, int $offset, int $length, int $pri = \EIO_PRI_DEFAULT, callable $callback = null, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_fallocate($fd, $mode, $offset, $length, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_fchmod($fd, int $mode, int $pri = \EIO_PRI_DEFAULT, callable $callback = null, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_fchmod($fd, $mode, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_fchown($fd, int $uid, int $gid = -1, int $pri = \EIO_PRI_DEFAULT, callable $callback = null, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_fchown($fd, $uid, $gid, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_fdatasync($fd, int $pri = \EIO_PRI_DEFAULT, callable $callback = null, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_fdatasync($fd, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_fstat($fd, int $pri, callable $callback, $data = null)
{
    \error_clear_last();
    if ($data !== null) {
        $safeResult = \eio_fstat($fd, $pri, $callback, $data);
    } else {
        $safeResult = \eio_fstat($fd, $pri, $callback);
    }
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_fstatvfs($fd, int $pri, callable $callback, $data = null)
{
    \error_clear_last();
    if ($data !== null) {
        $safeResult = \eio_fstatvfs($fd, $pri, $callback, $data);
    } else {
        $safeResult = \eio_fstatvfs($fd, $pri, $callback);
    }
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_fsync($fd, int $pri = \EIO_PRI_DEFAULT, callable $callback = null, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_fsync($fd, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_ftruncate($fd, int $offset = 0, int $pri = \EIO_PRI_DEFAULT, callable $callback = null, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_ftruncate($fd, $offset, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_futime($fd, float $atime, float $mtime, int $pri = \EIO_PRI_DEFAULT, callable $callback = null, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_futime($fd, $atime, $mtime, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_grp(callable $callback, string $data = null)
{
    \error_clear_last();
    $safeResult = \eio_grp($callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_lstat(string $path, int $pri, callable $callback, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_lstat($path, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_mkdir(string $path, int $mode, int $pri = \EIO_PRI_DEFAULT, callable $callback = null, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_mkdir($path, $mode, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_mknod(string $path, int $mode, int $dev, int $pri = \EIO_PRI_DEFAULT, callable $callback = null, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_mknod($path, $mode, $dev, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_nop(int $pri = \EIO_PRI_DEFAULT, callable $callback = null, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_nop($pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_readahead($fd, int $offset, int $length, int $pri = \EIO_PRI_DEFAULT, callable $callback = null, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_readahead($fd, $offset, $length, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_readdir(string $path, int $flags, int $pri, callable $callback, string $data = null)
{
    \error_clear_last();
    $safeResult = \eio_readdir($path, $flags, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_readlink(string $path, int $pri, callable $callback, string $data = null)
{
    \error_clear_last();
    $safeResult = \eio_readlink($path, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_rename(string $path, string $new_path, int $pri = \EIO_PRI_DEFAULT, callable $callback = null, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_rename($path, $new_path, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_rmdir(string $path, int $pri = \EIO_PRI_DEFAULT, callable $callback = null, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_rmdir($path, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_seek($fd, int $offset, int $whence, int $pri = \EIO_PRI_DEFAULT, callable $callback = null, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_seek($fd, $offset, $whence, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_sendfile($out_fd, $in_fd, int $offset, int $length, int $pri = null, callable $callback = null, string $data = null)
{
    \error_clear_last();
    if ($data !== null) {
        $safeResult = \eio_sendfile($out_fd, $in_fd, $offset, $length, $pri, $callback, $data);
    } elseif ($callback !== null) {
        $safeResult = \eio_sendfile($out_fd, $in_fd, $offset, $length, $pri, $callback);
    } elseif ($pri !== null) {
        $safeResult = \eio_sendfile($out_fd, $in_fd, $offset, $length, $pri);
    } else {
        $safeResult = \eio_sendfile($out_fd, $in_fd, $offset, $length);
    }
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_stat(string $path, int $pri, callable $callback, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_stat($path, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_statvfs(string $path, int $pri, callable $callback, $data = null)
{
    \error_clear_last();
    if ($data !== null) {
        $safeResult = \eio_statvfs($path, $pri, $callback, $data);
    } else {
        $safeResult = \eio_statvfs($path, $pri, $callback);
    }
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_symlink(string $path, string $new_path, int $pri = \EIO_PRI_DEFAULT, callable $callback = null, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_symlink($path, $new_path, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_sync_file_range($fd, int $offset, int $nbytes, int $flags, int $pri = \EIO_PRI_DEFAULT, callable $callback = null, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_sync_file_range($fd, $offset, $nbytes, $flags, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_sync(int $pri = \EIO_PRI_DEFAULT, callable $callback = null, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_sync($pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_syncfs($fd, int $pri = \EIO_PRI_DEFAULT, callable $callback = null, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_syncfs($fd, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_truncate(string $path, int $offset = 0, int $pri = \EIO_PRI_DEFAULT, callable $callback = null, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_truncate($path, $offset, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_unlink(string $path, int $pri = \EIO_PRI_DEFAULT, callable $callback = null, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_unlink($path, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_utime(string $path, float $atime, float $mtime, int $pri = \EIO_PRI_DEFAULT, callable $callback = null, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_utime($path, $atime, $mtime, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
function eio_write($fd, string $str, int $length = 0, int $offset = 0, int $pri = \EIO_PRI_DEFAULT, callable $callback = null, $data = null)
{
    \error_clear_last();
    $safeResult = \eio_write($fd, $str, $length, $offset, $pri, $callback, $data);
    if ($safeResult === \false) {
        throw EioException::createFromPhpError();
    }
    return $safeResult;
}
