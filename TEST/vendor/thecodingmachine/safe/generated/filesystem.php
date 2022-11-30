<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\FilesystemException;
function chgrp(string $filename, $group) : void
{
    \error_clear_last();
    $result = \chgrp($filename, $group);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function chmod(string $filename, int $permissions) : void
{
    \error_clear_last();
    $result = \chmod($filename, $permissions);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function chown(string $filename, $user) : void
{
    \error_clear_last();
    $result = \chown($filename, $user);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function copy(string $from, string $to, $context = null) : void
{
    \error_clear_last();
    if ($context !== null) {
        $result = \copy($from, $to, $context);
    } else {
        $result = \copy($from, $to);
    }
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function disk_free_space(string $directory) : float
{
    \error_clear_last();
    $result = \disk_free_space($directory);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $result;
}
function disk_total_space(string $directory) : float
{
    \error_clear_last();
    $result = \disk_total_space($directory);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $result;
}
function fclose($stream) : void
{
    \error_clear_last();
    $result = \fclose($stream);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function fdatasync($stream) : void
{
    \error_clear_last();
    $result = \fdatasync($stream);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function fflush($stream) : void
{
    \error_clear_last();
    $result = \fflush($stream);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function fgetcsv($stream, int $length = null, string $separator = ",", string $enclosure = "\"", string $escape = "\\")
{
    \error_clear_last();
    if ($escape !== "\\") {
        $result = \fgetcsv($stream, $length, $separator, $enclosure, $escape);
    } elseif ($enclosure !== "\"") {
        $result = \fgetcsv($stream, $length, $separator, $enclosure);
    } elseif ($separator !== ",") {
        $result = \fgetcsv($stream, $length, $separator);
    } elseif ($length !== null) {
        $result = \fgetcsv($stream, $length);
    } else {
        $result = \fgetcsv($stream);
    }
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $result;
}
function file_get_contents(string $filename, bool $use_include_path = \false, $context = null, int $offset = 0, int $length = null) : string
{
    \error_clear_last();
    if ($length !== null) {
        $result = \file_get_contents($filename, $use_include_path, $context, $offset, $length);
    } elseif ($offset !== 0) {
        $result = \file_get_contents($filename, $use_include_path, $context, $offset);
    } elseif ($context !== null) {
        $result = \file_get_contents($filename, $use_include_path, $context);
    } else {
        $result = \file_get_contents($filename, $use_include_path);
    }
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $result;
}
function file_put_contents(string $filename, $data, int $flags = 0, $context = null) : int
{
    \error_clear_last();
    if ($context !== null) {
        $result = \file_put_contents($filename, $data, $flags, $context);
    } else {
        $result = \file_put_contents($filename, $data, $flags);
    }
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $result;
}
function file(string $filename, int $flags = 0, $context = null) : array
{
    \error_clear_last();
    if ($context !== null) {
        $result = \file($filename, $flags, $context);
    } else {
        $result = \file($filename, $flags);
    }
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $result;
}
function fileatime(string $filename) : int
{
    \error_clear_last();
    $result = \fileatime($filename);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $result;
}
function filectime(string $filename) : int
{
    \error_clear_last();
    $result = \filectime($filename);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $result;
}
function fileinode(string $filename) : int
{
    \error_clear_last();
    $result = \fileinode($filename);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $result;
}
function filemtime(string $filename) : int
{
    \error_clear_last();
    $result = \filemtime($filename);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $result;
}
function fileowner(string $filename) : int
{
    \error_clear_last();
    $result = \fileowner($filename);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $result;
}
function fileperms(string $filename) : int
{
    \error_clear_last();
    $result = \fileperms($filename);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $result;
}
function filesize(string $filename) : int
{
    \error_clear_last();
    $result = \filesize($filename);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $result;
}
function flock($stream, int $operation, ?int &$would_block = null) : void
{
    \error_clear_last();
    $result = \flock($stream, $operation, $would_block);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function fopen(string $filename, string $mode, bool $use_include_path = \false, $context = null)
{
    \error_clear_last();
    if ($context !== null) {
        $result = \fopen($filename, $mode, $use_include_path, $context);
    } else {
        $result = \fopen($filename, $mode, $use_include_path);
    }
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $result;
}
function fread($stream, int $length) : string
{
    \error_clear_last();
    $result = \fread($stream, $length);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $result;
}
function fstat($stream) : array
{
    \error_clear_last();
    $result = \fstat($stream);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $result;
}
function fsync($stream) : void
{
    \error_clear_last();
    $result = \fsync($stream);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function ftruncate($stream, int $size) : void
{
    \error_clear_last();
    $result = \ftruncate($stream, $size);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function fwrite($stream, string $data, int $length = null) : int
{
    \error_clear_last();
    if ($length !== null) {
        $result = \fwrite($stream, $data, $length);
    } else {
        $result = \fwrite($stream, $data);
    }
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $result;
}
function glob(string $pattern, int $flags = 0) : array
{
    \error_clear_last();
    $result = \glob($pattern, $flags);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $result;
}
function lchgrp(string $filename, $group) : void
{
    \error_clear_last();
    $result = \lchgrp($filename, $group);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function lchown(string $filename, $user) : void
{
    \error_clear_last();
    $result = \lchown($filename, $user);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function link(string $target, string $link) : void
{
    \error_clear_last();
    $result = \link($target, $link);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function lstat(string $filename) : array
{
    \error_clear_last();
    $result = \lstat($filename);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $result;
}
function mkdir(string $directory, int $permissions = 0777, bool $recursive = \false, $context = null) : void
{
    \error_clear_last();
    if ($context !== null) {
        $result = \mkdir($directory, $permissions, $recursive, $context);
    } else {
        $result = \mkdir($directory, $permissions, $recursive);
    }
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function parse_ini_file(string $filename, bool $process_sections = \false, int $scanner_mode = \INI_SCANNER_NORMAL) : array
{
    \error_clear_last();
    $result = \parse_ini_file($filename, $process_sections, $scanner_mode);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $result;
}
function parse_ini_string(string $ini_string, bool $process_sections = \false, int $scanner_mode = \INI_SCANNER_NORMAL) : array
{
    \error_clear_last();
    $result = \parse_ini_string($ini_string, $process_sections, $scanner_mode);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $result;
}
function readfile(string $filename, bool $use_include_path = \false, $context = null) : int
{
    \error_clear_last();
    if ($context !== null) {
        $result = \readfile($filename, $use_include_path, $context);
    } else {
        $result = \readfile($filename, $use_include_path);
    }
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $result;
}
function readlink(string $path) : string
{
    \error_clear_last();
    $result = \readlink($path);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $result;
}
function realpath(string $path) : string
{
    \error_clear_last();
    $result = \realpath($path);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $result;
}
function rename(string $from, string $to, $context = null) : void
{
    \error_clear_last();
    if ($context !== null) {
        $result = \rename($from, $to, $context);
    } else {
        $result = \rename($from, $to);
    }
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function rewind($stream) : void
{
    \error_clear_last();
    $result = \rewind($stream);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function rmdir(string $directory, $context = null) : void
{
    \error_clear_last();
    if ($context !== null) {
        $result = \rmdir($directory, $context);
    } else {
        $result = \rmdir($directory);
    }
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function symlink(string $target, string $link) : void
{
    \error_clear_last();
    $result = \symlink($target, $link);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function tempnam(string $directory, string $prefix) : string
{
    \error_clear_last();
    $result = \tempnam($directory, $prefix);
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $result;
}
function tmpfile()
{
    \error_clear_last();
    $result = \tmpfile();
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
    return $result;
}
function touch(string $filename, int $mtime = null, int $atime = null) : void
{
    \error_clear_last();
    if ($atime !== null) {
        $result = \touch($filename, $mtime, $atime);
    } elseif ($mtime !== null) {
        $result = \touch($filename, $mtime);
    } else {
        $result = \touch($filename);
    }
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
function unlink(string $filename, $context = null) : void
{
    \error_clear_last();
    if ($context !== null) {
        $result = \unlink($filename, $context);
    } else {
        $result = \unlink($filename);
    }
    if ($result === \false) {
        throw FilesystemException::createFromPhpError();
    }
}
