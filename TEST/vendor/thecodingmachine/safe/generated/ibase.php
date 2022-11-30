<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\IbaseException;
function fbird_blob_cancel($blob_handle) : void
{
    \error_clear_last();
    $result = \fbird_blob_cancel($blob_handle);
    if ($result === \false) {
        throw IbaseException::createFromPhpError();
    }
}
function ibase_add_user($service_handle, string $user_name, string $password, string $first_name = null, string $middle_name = null, string $last_name = null) : void
{
    \error_clear_last();
    if ($last_name !== null) {
        $result = \ibase_add_user($service_handle, $user_name, $password, $first_name, $middle_name, $last_name);
    } elseif ($middle_name !== null) {
        $result = \ibase_add_user($service_handle, $user_name, $password, $first_name, $middle_name);
    } elseif ($first_name !== null) {
        $result = \ibase_add_user($service_handle, $user_name, $password, $first_name);
    } else {
        $result = \ibase_add_user($service_handle, $user_name, $password);
    }
    if ($result === \false) {
        throw IbaseException::createFromPhpError();
    }
}
function ibase_backup($service_handle, string $source_db, string $dest_file, int $options = 0, bool $verbose = \false)
{
    \error_clear_last();
    $result = \ibase_backup($service_handle, $source_db, $dest_file, $options, $verbose);
    if ($result === \false) {
        throw IbaseException::createFromPhpError();
    }
    return $result;
}
function ibase_blob_cancel($blob_handle) : void
{
    \error_clear_last();
    $result = \ibase_blob_cancel($blob_handle);
    if ($result === \false) {
        throw IbaseException::createFromPhpError();
    }
}
function ibase_blob_create($link_identifier = null)
{
    \error_clear_last();
    if ($link_identifier !== null) {
        $result = \ibase_blob_create($link_identifier);
    } else {
        $result = \ibase_blob_create();
    }
    if ($result === \false) {
        throw IbaseException::createFromPhpError();
    }
    return $result;
}
function ibase_blob_get($blob_handle, int $len) : string
{
    \error_clear_last();
    $result = \ibase_blob_get($blob_handle, $len);
    if ($result === \false) {
        throw IbaseException::createFromPhpError();
    }
    return $result;
}
function ibase_close($connection_id = null) : void
{
    \error_clear_last();
    if ($connection_id !== null) {
        $result = \ibase_close($connection_id);
    } else {
        $result = \ibase_close();
    }
    if ($result === \false) {
        throw IbaseException::createFromPhpError();
    }
}
function ibase_commit_ret($link_or_trans_identifier = null) : void
{
    \error_clear_last();
    if ($link_or_trans_identifier !== null) {
        $result = \ibase_commit_ret($link_or_trans_identifier);
    } else {
        $result = \ibase_commit_ret();
    }
    if ($result === \false) {
        throw IbaseException::createFromPhpError();
    }
}
function ibase_commit($link_or_trans_identifier = null) : void
{
    \error_clear_last();
    if ($link_or_trans_identifier !== null) {
        $result = \ibase_commit($link_or_trans_identifier);
    } else {
        $result = \ibase_commit();
    }
    if ($result === \false) {
        throw IbaseException::createFromPhpError();
    }
}
function ibase_connect(string $database = null, string $username = null, string $password = null, string $charset = null, int $buffers = null, int $dialect = null, string $role = null, int $sync = null)
{
    \error_clear_last();
    if ($sync !== null) {
        $result = \ibase_connect($database, $username, $password, $charset, $buffers, $dialect, $role, $sync);
    } elseif ($role !== null) {
        $result = \ibase_connect($database, $username, $password, $charset, $buffers, $dialect, $role);
    } elseif ($dialect !== null) {
        $result = \ibase_connect($database, $username, $password, $charset, $buffers, $dialect);
    } elseif ($buffers !== null) {
        $result = \ibase_connect($database, $username, $password, $charset, $buffers);
    } elseif ($charset !== null) {
        $result = \ibase_connect($database, $username, $password, $charset);
    } elseif ($password !== null) {
        $result = \ibase_connect($database, $username, $password);
    } elseif ($username !== null) {
        $result = \ibase_connect($database, $username);
    } elseif ($database !== null) {
        $result = \ibase_connect($database);
    } else {
        $result = \ibase_connect();
    }
    if ($result === \false) {
        throw IbaseException::createFromPhpError();
    }
    return $result;
}
function ibase_delete_user($service_handle, string $user_name) : void
{
    \error_clear_last();
    $result = \ibase_delete_user($service_handle, $user_name);
    if ($result === \false) {
        throw IbaseException::createFromPhpError();
    }
}
function ibase_drop_db($connection = null) : void
{
    \error_clear_last();
    if ($connection !== null) {
        $result = \ibase_drop_db($connection);
    } else {
        $result = \ibase_drop_db();
    }
    if ($result === \false) {
        throw IbaseException::createFromPhpError();
    }
}
function ibase_free_event_handler($event) : void
{
    \error_clear_last();
    $result = \ibase_free_event_handler($event);
    if ($result === \false) {
        throw IbaseException::createFromPhpError();
    }
}
function ibase_free_query($query) : void
{
    \error_clear_last();
    $result = \ibase_free_query($query);
    if ($result === \false) {
        throw IbaseException::createFromPhpError();
    }
}
function ibase_free_result($result_identifier) : void
{
    \error_clear_last();
    $result = \ibase_free_result($result_identifier);
    if ($result === \false) {
        throw IbaseException::createFromPhpError();
    }
}
function ibase_maintain_db($service_handle, string $db, int $action, int $argument = 0) : void
{
    \error_clear_last();
    $result = \ibase_maintain_db($service_handle, $db, $action, $argument);
    if ($result === \false) {
        throw IbaseException::createFromPhpError();
    }
}
function ibase_modify_user($service_handle, string $user_name, string $password, string $first_name = null, string $middle_name = null, string $last_name = null) : void
{
    \error_clear_last();
    if ($last_name !== null) {
        $result = \ibase_modify_user($service_handle, $user_name, $password, $first_name, $middle_name, $last_name);
    } elseif ($middle_name !== null) {
        $result = \ibase_modify_user($service_handle, $user_name, $password, $first_name, $middle_name);
    } elseif ($first_name !== null) {
        $result = \ibase_modify_user($service_handle, $user_name, $password, $first_name);
    } else {
        $result = \ibase_modify_user($service_handle, $user_name, $password);
    }
    if ($result === \false) {
        throw IbaseException::createFromPhpError();
    }
}
function ibase_name_result($result, string $name) : void
{
    \error_clear_last();
    $result = \ibase_name_result($result, $name);
    if ($result === \false) {
        throw IbaseException::createFromPhpError();
    }
}
function ibase_pconnect(string $database = null, string $username = null, string $password = null, string $charset = null, int $buffers = null, int $dialect = null, string $role = null, int $sync = null)
{
    \error_clear_last();
    if ($sync !== null) {
        $result = \ibase_pconnect($database, $username, $password, $charset, $buffers, $dialect, $role, $sync);
    } elseif ($role !== null) {
        $result = \ibase_pconnect($database, $username, $password, $charset, $buffers, $dialect, $role);
    } elseif ($dialect !== null) {
        $result = \ibase_pconnect($database, $username, $password, $charset, $buffers, $dialect);
    } elseif ($buffers !== null) {
        $result = \ibase_pconnect($database, $username, $password, $charset, $buffers);
    } elseif ($charset !== null) {
        $result = \ibase_pconnect($database, $username, $password, $charset);
    } elseif ($password !== null) {
        $result = \ibase_pconnect($database, $username, $password);
    } elseif ($username !== null) {
        $result = \ibase_pconnect($database, $username);
    } elseif ($database !== null) {
        $result = \ibase_pconnect($database);
    } else {
        $result = \ibase_pconnect();
    }
    if ($result === \false) {
        throw IbaseException::createFromPhpError();
    }
    return $result;
}
function ibase_restore($service_handle, string $source_file, string $dest_db, int $options = 0, bool $verbose = \false)
{
    \error_clear_last();
    $result = \ibase_restore($service_handle, $source_file, $dest_db, $options, $verbose);
    if ($result === \false) {
        throw IbaseException::createFromPhpError();
    }
    return $result;
}
function ibase_rollback_ret($link_or_trans_identifier = null) : void
{
    \error_clear_last();
    if ($link_or_trans_identifier !== null) {
        $result = \ibase_rollback_ret($link_or_trans_identifier);
    } else {
        $result = \ibase_rollback_ret();
    }
    if ($result === \false) {
        throw IbaseException::createFromPhpError();
    }
}
function ibase_rollback($link_or_trans_identifier = null) : void
{
    \error_clear_last();
    if ($link_or_trans_identifier !== null) {
        $result = \ibase_rollback($link_or_trans_identifier);
    } else {
        $result = \ibase_rollback();
    }
    if ($result === \false) {
        throw IbaseException::createFromPhpError();
    }
}
function ibase_service_attach(string $host, string $dba_username, string $dba_password)
{
    \error_clear_last();
    $result = \ibase_service_attach($host, $dba_username, $dba_password);
    if ($result === \false) {
        throw IbaseException::createFromPhpError();
    }
    return $result;
}
function ibase_service_detach($service_handle) : void
{
    \error_clear_last();
    $result = \ibase_service_detach($service_handle);
    if ($result === \false) {
        throw IbaseException::createFromPhpError();
    }
}
