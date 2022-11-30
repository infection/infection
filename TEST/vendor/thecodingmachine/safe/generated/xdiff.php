<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\XdiffException;
function xdiff_file_bdiff(string $old_file, string $new_file, string $dest) : void
{
    \error_clear_last();
    $result = \xdiff_file_bdiff($old_file, $new_file, $dest);
    if ($result === \false) {
        throw XdiffException::createFromPhpError();
    }
}
function xdiff_file_bpatch(string $file, string $patch, string $dest) : void
{
    \error_clear_last();
    $result = \xdiff_file_bpatch($file, $patch, $dest);
    if ($result === \false) {
        throw XdiffException::createFromPhpError();
    }
}
function xdiff_file_diff_binary(string $old_file, string $new_file, string $dest) : void
{
    \error_clear_last();
    $result = \xdiff_file_diff_binary($old_file, $new_file, $dest);
    if ($result === \false) {
        throw XdiffException::createFromPhpError();
    }
}
function xdiff_file_diff(string $old_file, string $new_file, string $dest, int $context = 3, bool $minimal = \false) : void
{
    \error_clear_last();
    $result = \xdiff_file_diff($old_file, $new_file, $dest, $context, $minimal);
    if ($result === \false) {
        throw XdiffException::createFromPhpError();
    }
}
function xdiff_file_patch_binary(string $file, string $patch, string $dest) : void
{
    \error_clear_last();
    $result = \xdiff_file_patch_binary($file, $patch, $dest);
    if ($result === \false) {
        throw XdiffException::createFromPhpError();
    }
}
function xdiff_file_rabdiff(string $old_file, string $new_file, string $dest) : void
{
    \error_clear_last();
    $result = \xdiff_file_rabdiff($old_file, $new_file, $dest);
    if ($result === \false) {
        throw XdiffException::createFromPhpError();
    }
}
function xdiff_string_bpatch(string $str, string $patch) : string
{
    \error_clear_last();
    $result = \xdiff_string_bpatch($str, $patch);
    if ($result === \false) {
        throw XdiffException::createFromPhpError();
    }
    return $result;
}
function xdiff_string_patch_binary(string $str, string $patch) : string
{
    \error_clear_last();
    $result = \xdiff_string_patch_binary($str, $patch);
    if ($result === \false) {
        throw XdiffException::createFromPhpError();
    }
    return $result;
}
function xdiff_string_patch(string $str, string $patch, int $flags = null, ?string &$error = null) : string
{
    \error_clear_last();
    if ($error !== null) {
        $result = \xdiff_string_patch($str, $patch, $flags, $error);
    } elseif ($flags !== null) {
        $result = \xdiff_string_patch($str, $patch, $flags);
    } else {
        $result = \xdiff_string_patch($str, $patch);
    }
    if ($result === \false) {
        throw XdiffException::createFromPhpError();
    }
    return $result;
}
