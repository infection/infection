<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\Ssh2Exception;
function ssh2_auth_agent($session, string $username) : void
{
    \error_clear_last();
    $safeResult = \ssh2_auth_agent($session, $username);
    if ($safeResult === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_auth_hostbased_file($session, string $username, string $hostname, string $pubkeyfile, string $privkeyfile, string $passphrase = null, string $local_username = null) : void
{
    \error_clear_last();
    if ($local_username !== null) {
        $safeResult = \ssh2_auth_hostbased_file($session, $username, $hostname, $pubkeyfile, $privkeyfile, $passphrase, $local_username);
    } elseif ($passphrase !== null) {
        $safeResult = \ssh2_auth_hostbased_file($session, $username, $hostname, $pubkeyfile, $privkeyfile, $passphrase);
    } else {
        $safeResult = \ssh2_auth_hostbased_file($session, $username, $hostname, $pubkeyfile, $privkeyfile);
    }
    if ($safeResult === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_auth_password($session, string $username, string $password) : void
{
    \error_clear_last();
    $safeResult = \ssh2_auth_password($session, $username, $password);
    if ($safeResult === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_auth_pubkey_file($session, string $username, string $pubkeyfile, string $privkeyfile, string $passphrase = null) : void
{
    \error_clear_last();
    if ($passphrase !== null) {
        $safeResult = \ssh2_auth_pubkey_file($session, $username, $pubkeyfile, $privkeyfile, $passphrase);
    } else {
        $safeResult = \ssh2_auth_pubkey_file($session, $username, $pubkeyfile, $privkeyfile);
    }
    if ($safeResult === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_connect(string $host, int $port = 22, array $methods = null, array $callbacks = null)
{
    \error_clear_last();
    if ($callbacks !== null) {
        $safeResult = \ssh2_connect($host, $port, $methods, $callbacks);
    } elseif ($methods !== null) {
        $safeResult = \ssh2_connect($host, $port, $methods);
    } else {
        $safeResult = \ssh2_connect($host, $port);
    }
    if ($safeResult === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
    return $safeResult;
}
function ssh2_disconnect($session) : void
{
    \error_clear_last();
    $safeResult = \ssh2_disconnect($session);
    if ($safeResult === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_exec($session, string $command, string $pty = null, array $env = null, int $width = 80, int $height = 25, int $width_height_type = \SSH2_TERM_UNIT_CHARS)
{
    \error_clear_last();
    if ($width_height_type !== \SSH2_TERM_UNIT_CHARS) {
        $safeResult = \ssh2_exec($session, $command, $pty, $env, $width, $height, $width_height_type);
    } elseif ($height !== 25) {
        $safeResult = \ssh2_exec($session, $command, $pty, $env, $width, $height);
    } elseif ($width !== 80) {
        $safeResult = \ssh2_exec($session, $command, $pty, $env, $width);
    } elseif ($env !== null) {
        $safeResult = \ssh2_exec($session, $command, $pty, $env);
    } elseif ($pty !== null) {
        $safeResult = \ssh2_exec($session, $command, $pty);
    } else {
        $safeResult = \ssh2_exec($session, $command);
    }
    if ($safeResult === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
    return $safeResult;
}
function ssh2_forward_accept($listener)
{
    \error_clear_last();
    $safeResult = \ssh2_forward_accept($listener);
    if ($safeResult === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
    return $safeResult;
}
function ssh2_forward_listen($session, int $port, string $host = null, int $max_connections = 16)
{
    \error_clear_last();
    if ($max_connections !== 16) {
        $safeResult = \ssh2_forward_listen($session, $port, $host, $max_connections);
    } elseif ($host !== null) {
        $safeResult = \ssh2_forward_listen($session, $port, $host);
    } else {
        $safeResult = \ssh2_forward_listen($session, $port);
    }
    if ($safeResult === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
    return $safeResult;
}
function ssh2_publickey_add($pkey, string $algoname, string $blob, bool $overwrite = \false, array $attributes = null) : void
{
    \error_clear_last();
    if ($attributes !== null) {
        $safeResult = \ssh2_publickey_add($pkey, $algoname, $blob, $overwrite, $attributes);
    } else {
        $safeResult = \ssh2_publickey_add($pkey, $algoname, $blob, $overwrite);
    }
    if ($safeResult === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_publickey_init($session)
{
    \error_clear_last();
    $safeResult = \ssh2_publickey_init($session);
    if ($safeResult === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
    return $safeResult;
}
function ssh2_publickey_remove($pkey, string $algoname, string $blob) : void
{
    \error_clear_last();
    $safeResult = \ssh2_publickey_remove($pkey, $algoname, $blob);
    if ($safeResult === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_scp_recv($session, string $remote_file, string $local_file) : void
{
    \error_clear_last();
    $safeResult = \ssh2_scp_recv($session, $remote_file, $local_file);
    if ($safeResult === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_scp_send($session, string $local_file, string $remote_file, int $create_mode = 0644) : void
{
    \error_clear_last();
    $safeResult = \ssh2_scp_send($session, $local_file, $remote_file, $create_mode);
    if ($safeResult === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_send_eof($channel) : void
{
    \error_clear_last();
    $safeResult = \_HumbugBoxb47773b41c19\ssh2_send_eof($channel);
    if ($safeResult === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_sftp_chmod($sftp, string $filename, int $mode) : void
{
    \error_clear_last();
    $safeResult = \ssh2_sftp_chmod($sftp, $filename, $mode);
    if ($safeResult === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_sftp_mkdir($sftp, string $dirname, int $mode = 0777, bool $recursive = \false) : void
{
    \error_clear_last();
    $safeResult = \ssh2_sftp_mkdir($sftp, $dirname, $mode, $recursive);
    if ($safeResult === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_sftp_rename($sftp, string $from, string $to) : void
{
    \error_clear_last();
    $safeResult = \ssh2_sftp_rename($sftp, $from, $to);
    if ($safeResult === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_sftp_rmdir($sftp, string $dirname) : void
{
    \error_clear_last();
    $safeResult = \ssh2_sftp_rmdir($sftp, $dirname);
    if ($safeResult === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_sftp_symlink($sftp, string $target, string $link) : void
{
    \error_clear_last();
    $safeResult = \ssh2_sftp_symlink($sftp, $target, $link);
    if ($safeResult === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_sftp_unlink($sftp, string $filename) : void
{
    \error_clear_last();
    $safeResult = \ssh2_sftp_unlink($sftp, $filename);
    if ($safeResult === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
}
function ssh2_sftp($session)
{
    \error_clear_last();
    $safeResult = \ssh2_sftp($session);
    if ($safeResult === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
    return $safeResult;
}
function ssh2_shell($session, string $term_type = "vanilla", array $env = null, int $width = 80, int $height = 25, int $width_height_type = \SSH2_TERM_UNIT_CHARS)
{
    \error_clear_last();
    if ($width_height_type !== \SSH2_TERM_UNIT_CHARS) {
        $safeResult = \ssh2_shell($session, $term_type, $env, $width, $height, $width_height_type);
    } elseif ($height !== 25) {
        $safeResult = \ssh2_shell($session, $term_type, $env, $width, $height);
    } elseif ($width !== 80) {
        $safeResult = \ssh2_shell($session, $term_type, $env, $width);
    } elseif ($env !== null) {
        $safeResult = \ssh2_shell($session, $term_type, $env);
    } else {
        $safeResult = \ssh2_shell($session, $term_type);
    }
    if ($safeResult === \false) {
        throw Ssh2Exception::createFromPhpError();
    }
    return $safeResult;
}
