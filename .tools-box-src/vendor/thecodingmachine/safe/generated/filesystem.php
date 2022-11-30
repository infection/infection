<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\FilesystemException;
function chgrp(string $filename, $group) : void
{
    \error_clear_last();
    $safeResult = \chgrp($filename, $group);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function chmod(string $filename, int $permissions) : void
{
    \error_clear_last();
    $safeResult = \chmod($filename, $permissions);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function chown(string $filename, $user) : void
{
    \error_clear_last();
    $safeResult = \chown($filename, $user);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function copy(string $from, string $to, $context = null) : void
{
    \error_clear_last();
    if ($context !== null) {
        $safeResult = \copy($from, $to, $context);
    } else {
        $safeResult = \copy($from, $to);
    }
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function disk_free_space(string $directory) : float
{
    \error_clear_last();
    $safeResult = \disk_free_space($directory);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $safeResult;
}
function disk_total_space(string $directory) : float
{
    \error_clear_last();
    $safeResult = \disk_total_space($directory);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $safeResult;
}
function fclose($stream) : void
{
    \error_clear_last();
    $safeResult = \fclose($stream);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function fdatasync($stream) : void
{
    \error_clear_last();
    $safeResult = \fdatasync($stream);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function fflush($stream) : void
{
    \error_clear_last();
    $safeResult = \fflush($stream);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function file_get_contents(string $filename, bool $use_include_path = \false, $context = null, int $offset = 0, int $length = null) : string
{
    \error_clear_last();
    if ($length !== null) {
        $safeResult = \file_get_contents($filename, $use_include_path, $context, $offset, $length);
    } elseif ($offset !== 0) {
        $safeResult = \file_get_contents($filename, $use_include_path, $context, $offset);
    } elseif ($context !== null) {
        $safeResult = \file_get_contents($filename, $use_include_path, $context);
    } else {
        $safeResult = \file_get_contents($filename, $use_include_path);
    }
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $safeResult;
}
function file_put_contents(string $filename, $data, int $flags = 0, $context = null) : int
{
    \error_clear_last();
    if ($context !== null) {
        $safeResult = \file_put_contents($filename, $data, $flags, $context);
    } else {
        $safeResult = \file_put_contents($filename, $data, $flags);
    }
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $safeResult;
}
function file(string $filename, int $flags = 0, $context = null) : array
{
    \error_clear_last();
    if ($context !== null) {
        $safeResult = \file($filename, $flags, $context);
    } else {
        $safeResult = \file($filename, $flags);
    }
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $safeResult;
}
function fileatime(string $filename) : int
{
    \error_clear_last();
    $safeResult = \fileatime($filename);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $safeResult;
}
function filectime(string $filename) : int
{
    \error_clear_last();
    $safeResult = \filectime($filename);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $safeResult;
}
function fileinode(string $filename) : int
{
    \error_clear_last();
    $safeResult = \fileinode($filename);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $safeResult;
}
function filemtime(string $filename) : int
{
    \error_clear_last();
    $safeResult = \filemtime($filename);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $safeResult;
}
function fileowner(string $filename) : int
{
    \error_clear_last();
    $safeResult = \fileowner($filename);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $safeResult;
}
function fileperms(string $filename) : int
{
    \error_clear_last();
    $safeResult = \fileperms($filename);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $safeResult;
}
function filesize(string $filename) : int
{
    \error_clear_last();
    $safeResult = \filesize($filename);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $safeResult;
}
function flock($stream, int $operation, ?int &$would_block = null) : void
{
    \error_clear_last();
    $safeResult = \flock($stream, $operation, $would_block);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function fopen(string $filename, string $mode, bool $use_include_path = \false, $context = null)
{
    \error_clear_last();
    if ($context !== null) {
        $safeResult = \fopen($filename, $mode, $use_include_path, $context);
    } else {
        $safeResult = \fopen($filename, $mode, $use_include_path);
    }
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $safeResult;
}
function fread($stream, int $length) : string
{
    \error_clear_last();
    $safeResult = \fread($stream, $length);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $safeResult;
}
function fstat($stream) : array
{
    \error_clear_last();
    $safeResult = \fstat($stream);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $safeResult;
}
function fsync($stream) : void
{
    \error_clear_last();
    $safeResult = \fsync($stream);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function ftruncate($stream, int $size) : void
{
    \error_clear_last();
    $safeResult = \ftruncate($stream, $size);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function fwrite($stream, string $data, int $length = null) : int
{
    \error_clear_last();
    if ($length !== null) {
        $safeResult = \fwrite($stream, $data, $length);
    } else {
        $safeResult = \fwrite($stream, $data);
    }
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $safeResult;
}
function glob(string $pattern, int $flags = 0) : array
{
    \error_clear_last();
    $safeResult = \glob($pattern, $flags);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $safeResult;
}
function lchgrp(string $filename, $group) : void
{
    \error_clear_last();
    $safeResult = \lchgrp($filename, $group);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function lchown(string $filename, $user) : void
{
    \error_clear_last();
    $safeResult = \lchown($filename, $user);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function link(string $target, string $link) : void
{
    \error_clear_last();
    $safeResult = \link($target, $link);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function lstat(string $filename) : array
{
    \error_clear_last();
    $safeResult = \lstat($filename);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $safeResult;
}
function mkdir(string $directory, int $permissions = 0777, bool $recursive = \false, $context = null) : void
{
    \error_clear_last();
    if ($context !== null) {
        $safeResult = \mkdir($directory, $permissions, $recursive, $context);
    } else {
        $safeResult = \mkdir($directory, $permissions, $recursive);
    }
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function parse_ini_file(string $filename, bool $process_sections = \false, int $scanner_mode = \INI_SCANNER_NORMAL) : array
{
    \error_clear_last();
    $safeResult = \parse_ini_file($filename, $process_sections, $scanner_mode);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $safeResult;
}
function parse_ini_string(string $ini_string, bool $process_sections = \false, int $scanner_mode = \INI_SCANNER_NORMAL) : array
{
    \error_clear_last();
    $safeResult = \parse_ini_string($ini_string, $process_sections, $scanner_mode);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $safeResult;
}
function readfile(string $filename, bool $use_include_path = \false, $context = null) : int
{
    \error_clear_last();
    if ($context !== null) {
        $safeResult = \readfile($filename, $use_include_path, $context);
    } else {
        $safeResult = \readfile($filename, $use_include_path);
    }
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $safeResult;
}
function readlink(string $path) : string
{
    \error_clear_last();
    $safeResult = \readlink($path);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $safeResult;
}
function realpath(string $path) : string
{
    \error_clear_last();
    $safeResult = \realpath($path);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $safeResult;
}
function rename(string $from, string $to, $context = null) : void
{
    \error_clear_last();
    if ($context !== null) {
        $safeResult = \rename($from, $to, $context);
    } else {
        $safeResult = \rename($from, $to);
    }
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function rewind($stream) : void
{
    \error_clear_last();
    $safeResult = \rewind($stream);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function rmdir(string $directory, $context = null) : void
{
    \error_clear_last();
    if ($context !== null) {
        $safeResult = \rmdir($directory, $context);
    } else {
        $safeResult = \rmdir($directory);
    }
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function symlink(string $target, string $link) : void
{
    \error_clear_last();
    $safeResult = \symlink($target, $link);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function tempnam(string $directory, string $prefix) : string
{
    \error_clear_last();
    $safeResult = \tempnam($directory, $prefix);
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $safeResult;
}
function tmpfile()
{
    \error_clear_last();
    $safeResult = \tmpfile();
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $safeResult;
}
function touch(string $filename, int $mtime = null, int $atime = null) : void
{
    \error_clear_last();
    if ($atime !== null) {
        $safeResult = \touch($filename, $mtime, $atime);
    } elseif ($mtime !== null) {
        $safeResult = \touch($filename, $mtime);
    } else {
        $safeResult = \touch($filename);
    }
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function unlink(string $filename, $context = null) : void
{
    \error_clear_last();
    if ($context !== null) {
        $safeResult = \unlink($filename, $context);
    } else {
        $safeResult = \unlink($filename);
    }
    if ($safeResult === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
