<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\Ssh2Exception;
function ssh2_auth_agent($session, string $username) : void
{
    \error_clear_last();
    $result = \ssh2_auth_agent($session, $username);
    if ($result === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_auth_hostbased_file($session, string $username, string $hostname, string $pubkeyfile, string $privkeyfile, string $passphrase = null, string $local_username = null) : void
{
    \error_clear_last();
    if ($local_username !== null) {
        $result = \ssh2_auth_hostbased_file($session, $username, $hostname, $pubkeyfile, $privkeyfile, $passphrase, $local_username);
    } elseif ($passphrase !== null) {
        $result = \ssh2_auth_hostbased_file($session, $username, $hostname, $pubkeyfile, $privkeyfile, $passphrase);
    } else {
        $result = \ssh2_auth_hostbased_file($session, $username, $hostname, $pubkeyfile, $privkeyfile);
    }
    if ($result === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_auth_password($session, string $username, string $password) : void
{
    \error_clear_last();
    $result = \ssh2_auth_password($session, $username, $password);
    if ($result === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_auth_pubkey_file($session, string $username, string $pubkeyfile, string $privkeyfile, string $passphrase = null) : void
{
    \error_clear_last();
    if ($passphrase !== null) {
        $result = \ssh2_auth_pubkey_file($session, $username, $pubkeyfile, $privkeyfile, $passphrase);
    } else {
        $result = \ssh2_auth_pubkey_file($session, $username, $pubkeyfile, $privkeyfile);
    }
    if ($result === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_connect(string $host, int $port = 22, array $methods = null, array $callbacks = null)
{
    \error_clear_last();
    if ($callbacks !== null) {
        $result = \ssh2_connect($host, $port, $methods, $callbacks);
    } elseif ($methods !== null) {
        $result = \ssh2_connect($host, $port, $methods);
    } else {
        $result = \ssh2_connect($host, $port);
    }
    if ($result === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
    return $result;
}
function ssh2_disconnect($session) : void
{
    \error_clear_last();
    $result = \ssh2_disconnect($session);
    if ($result === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_exec($session, string $command, string $pty = null, array $env = null, int $width = 80, int $height = 25, int $width_height_type = \SSH2_TERM_UNIT_CHARS)
{
    \error_clear_last();
    if ($width_height_type !== \SSH2_TERM_UNIT_CHARS) {
        $result = \ssh2_exec($session, $command, $pty, $env, $width, $height, $width_height_type);
    } elseif ($height !== 25) {
        $result = \ssh2_exec($session, $command, $pty, $env, $width, $height);
    } elseif ($width !== 80) {
        $result = \ssh2_exec($session, $command, $pty, $env, $width);
    } elseif ($env !== null) {
        $result = \ssh2_exec($session, $command, $pty, $env);
    } elseif ($pty !== null) {
        $result = \ssh2_exec($session, $command, $pty);
    } else {
        $result = \ssh2_exec($session, $command);
    }
    if ($result === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
    return $result;
}
function ssh2_forward_accept($listener)
{
    \error_clear_last();
    $result = \ssh2_forward_accept($listener);
    if ($result === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
    return $result;
}
function ssh2_forward_listen($session, int $port, string $host = null, int $max_connections = 16)
{
    \error_clear_last();
    if ($max_connections !== 16) {
        $result = \ssh2_forward_listen($session, $port, $host, $max_connections);
    } elseif ($host !== null) {
        $result = \ssh2_forward_listen($session, $port, $host);
    } else {
        $result = \ssh2_forward_listen($session, $port);
    }
    if ($result === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
    return $result;
}
function ssh2_publickey_add($pkey, string $algoname, string $blob, bool $overwrite = \false, array $attributes = null) : void
{
    \error_clear_last();
    if ($attributes !== null) {
        $result = \ssh2_publickey_add($pkey, $algoname, $blob, $overwrite, $attributes);
    } else {
        $result = \ssh2_publickey_add($pkey, $algoname, $blob, $overwrite);
    }
    if ($result === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_publickey_init($session)
{
    \error_clear_last();
    $result = \ssh2_publickey_init($session);
    if ($result === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
    return $result;
}
function ssh2_publickey_remove($pkey, string $algoname, string $blob) : void
{
    \error_clear_last();
    $result = \ssh2_publickey_remove($pkey, $algoname, $blob);
    if ($result === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_scp_recv($session, string $remote_file, string $local_file) : void
{
    \error_clear_last();
    $result = \ssh2_scp_recv($session, $remote_file, $local_file);
    if ($result === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_scp_send($session, string $local_file, string $remote_file, int $create_mode = 0644) : void
{
    \error_clear_last();
    $result = \ssh2_scp_send($session, $local_file, $remote_file, $create_mode);
    if ($result === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_send_eof($channel) : void
{
    \error_clear_last();
    $result = \_HumbugBox9658796bb9f0\ssh2_send_eof($channel);
    if ($result === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_sftp_chmod($sftp, string $filename, int $mode) : void
{
    \error_clear_last();
    $result = \ssh2_sftp_chmod($sftp, $filename, $mode);
    if ($result === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_sftp_mkdir($sftp, string $dirname, int $mode = 0777, bool $recursive = \false) : void
{
    \error_clear_last();
    $result = \ssh2_sftp_mkdir($sftp, $dirname, $mode, $recursive);
    if ($result === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_sftp_rename($sftp, string $from, string $to) : void
{
    \error_clear_last();
    $result = \ssh2_sftp_rename($sftp, $from, $to);
    if ($result === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_sftp_rmdir($sftp, string $dirname) : void
{
    \error_clear_last();
    $result = \ssh2_sftp_rmdir($sftp, $dirname);
    if ($result === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_sftp_symlink($sftp, string $target, string $link) : void
{
    \error_clear_last();
    $result = \ssh2_sftp_symlink($sftp, $target, $link);
    if ($result === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_sftp_unlink($sftp, string $filename) : void
{
    \error_clear_last();
    $result = \ssh2_sftp_unlink($sftp, $filename);
    if ($result === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_sftp($session)
{
    \error_clear_last();
    $result = \ssh2_sftp($session);
    if ($result === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
    return $result;
}
function ssh2_shell($session, string $term_type = "vanilla", array $env = null, int $width = 80, int $height = 25, int $width_height_type = \SSH2_TERM_UNIT_CHARS)
{
    \error_clear_last();
    if ($width_height_type !== \SSH2_TERM_UNIT_CHARS) {
        $result = \ssh2_shell($session, $term_type, $env, $width, $height, $width_height_type);
    } elseif ($height !== 25) {
        $result = \ssh2_shell($session, $term_type, $env, $width, $height);
    } elseif ($width !== 80) {
        $result = \ssh2_shell($session, $term_type, $env, $width);
    } elseif ($env !== null) {
        $result = \ssh2_shell($session, $term_type, $env);
    } else {
        $result = \ssh2_shell($session, $term_type);
    }
    if ($result === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
    return $result;
}
