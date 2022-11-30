<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\FtpException;
function ftp_alloc($ftp, int $size, ?string &$response = null) : void
{
    \error_clear_last();
    $result = \ftp_alloc($ftp, $size, $response);
    if ($result === \false) {
        throw FtpException::createFromPhpError();
    }
}
function ftp_append($ftp, string $remote_filename, string $local_filename, int $mode = \FTP_BINARY) : void
{
    \error_clear_last();
    $result = \ftp_append($ftp, $remote_filename, $local_filename, $mode);
    if ($result === \false) {
        throw FtpException::createFromPhpError();
    }
}
function ftp_cdup($ftp) : void
{
    \error_clear_last();
    $result = \ftp_cdup($ftp);
    if ($result === \false) {
        throw FtpException::createFromPhpError();
    }
}
function ftp_chdir($ftp, string $directory) : void
{
    \error_clear_last();
    $result = \ftp_chdir($ftp, $directory);
    if ($result === \false) {
        throw FtpException::createFromPhpError();
    }
}
function ftp_chmod($ftp, int $permissions, string $filename) : int
{
    \error_clear_last();
    $result = \ftp_chmod($ftp, $permissions, $filename);
    if ($result === \false) {
        throw FtpException::createFromPhpError();
    }
    return $result;
}
function ftp_close($ftp) : void
{
    \error_clear_last();
    $result = \ftp_close($ftp);
    if ($result === \false) {
        throw FtpException::createFromPhpError();
    }
}
function ftp_connect(string $hostname, int $port = 21, int $timeout = 90)
{
    \error_clear_last();
    $result = \ftp_connect($hostname, $port, $timeout);
    if ($result === \false) {
        throw FtpException::createFromPhpError();
    }
    return $result;
}
function ftp_delete($ftp, string $filename) : void
{
    \error_clear_last();
    $result = \ftp_delete($ftp, $filename);
    if ($result === \false) {
        throw FtpException::createFromPhpError();
    }
}
function ftp_fget($ftp, $stream, string $remote_filename, int $mode = \FTP_BINARY, int $offset = 0) : void
{
    \error_clear_last();
    $result = \ftp_fget($ftp, $stream, $remote_filename, $mode, $offset);
    if ($result === \false) {
        throw FtpException::createFromPhpError();
    }
}
function ftp_fput($ftp, string $remote_filename, $stream, int $mode = \FTP_BINARY, int $offset = 0) : void
{
    \error_clear_last();
    $result = \ftp_fput($ftp, $remote_filename, $stream, $mode, $offset);
    if ($result === \false) {
        throw FtpException::createFromPhpError();
    }
}
function ftp_get($ftp, string $local_filename, string $remote_filename, int $mode = \FTP_BINARY, int $offset = 0) : void
{
    \error_clear_last();
    $result = \ftp_get($ftp, $local_filename, $remote_filename, $mode, $offset);
    if ($result === \false) {
        throw FtpException::createFromPhpError();
    }
}
function ftp_login($ftp, string $username, string $password) : void
{
    \error_clear_last();
    $result = \ftp_login($ftp, $username, $password);
    if ($result === \false) {
        throw FtpException::createFromPhpError();
    }
}
function ftp_mkdir($ftp, string $directory) : string
{
    \error_clear_last();
    $result = \ftp_mkdir($ftp, $directory);
    if ($result === \false) {
        throw FtpException::createFromPhpError();
    }
    return $result;
}
function ftp_mlsd($ftp, string $directory) : array
{
    \error_clear_last();
    $result = \ftp_mlsd($ftp, $directory);
    if ($result === \false) {
        throw FtpException::createFromPhpError();
    }
    return $result;
}
function ftp_nb_put($ftp, string $remote_filename, string $local_filename, int $mode = \FTP_BINARY, int $offset = 0) : int
{
    \error_clear_last();
    $result = \ftp_nb_put($ftp, $remote_filename, $local_filename, $mode, $offset);
    if ($result === \false) {
        throw FtpException::createFromPhpError();
    }
    return $result;
}
function ftp_nlist($ftp, string $directory) : array
{
    \error_clear_last();
    $result = \ftp_nlist($ftp, $directory);
    if ($result === \false) {
        throw FtpException::createFromPhpError();
    }
    return $result;
}
function ftp_pasv($ftp, bool $enable) : void
{
    \error_clear_last();
    $result = \ftp_pasv($ftp, $enable);
    if ($result === \false) {
        throw FtpException::createFromPhpError();
    }
}
function ftp_put($ftp, string $remote_filename, string $local_filename, int $mode = \FTP_BINARY, int $offset = 0) : void
{
    \error_clear_last();
    $result = \ftp_put($ftp, $remote_filename, $local_filename, $mode, $offset);
    if ($result === \false) {
        throw FtpException::createFromPhpError();
    }
}
function ftp_pwd($ftp) : string
{
    \error_clear_last();
    $result = \ftp_pwd($ftp);
    if ($result === \false) {
        throw FtpException::createFromPhpError();
    }
    return $result;
}
function ftp_raw($ftp, string $command) : array
{
    \error_clear_last();
    $result = \ftp_raw($ftp, $command);
    if ($result === null) {
        throw FtpException::createFromPhpError();
    }
    return $result;
}
function ftp_rename($ftp, string $from, string $to) : void
{
    \error_clear_last();
    $result = \ftp_rename($ftp, $from, $to);
    if ($result === \false) {
        throw FtpException::createFromPhpError();
    }
}
function ftp_rmdir($ftp, string $directory) : void
{
    \error_clear_last();
    $result = \ftp_rmdir($ftp, $directory);
    if ($result === \false) {
        throw FtpException::createFromPhpError();
    }
}
function ftp_site($ftp, string $command) : void
{
    \error_clear_last();
    $result = \ftp_site($ftp, $command);
    if ($result === \false) {
        throw FtpException::createFromPhpError();
    }
}
function ftp_ssl_connect(string $hostname, int $port = 21, int $timeout = 90)
{
    \error_clear_last();
    $result = \ftp_ssl_connect($hostname, $port, $timeout);
    if ($result === \false) {
        throw FtpException::createFromPhpError();
    }
    return $result;
}
function ftp_systype($ftp) : string
{
    \error_clear_last();
    $result = \ftp_systype($ftp);
    if ($result === \false) {
        throw FtpException::createFromPhpError();
    }
    return $result;
}
