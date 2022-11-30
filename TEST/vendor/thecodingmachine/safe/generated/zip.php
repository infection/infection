<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\ZipException;
function zip_entry_close($zip_entry) : void
{
    \error_clear_last();
    $result = \zip_entry_close($zip_entry);
    if ($result === \false) {
        throw ZipException::createFromPhpError();
    }
}
function zip_entry_compressedsize($zip_entry) : int
{
    \error_clear_last();
    $result = \zip_entry_compressedsize($zip_entry);
    if ($result === \false) {
        throw ZipException::createFromPhpError();
    }
    return $result;
}
function zip_entry_compressionmethod($zip_entry) : string
{
    \error_clear_last();
    $result = \zip_entry_compressionmethod($zip_entry);
    if ($result === \false) {
        throw ZipException::createFromPhpError();
    }
    return $result;
}
function zip_entry_filesize($zip_entry) : int
{
    \error_clear_last();
    $result = \zip_entry_filesize($zip_entry);
    if ($result === \false) {
        throw ZipException::createFromPhpError();
    }
    return $result;
}
function zip_entry_name($zip_entry) : string
{
    \error_clear_last();
    $result = \zip_entry_name($zip_entry);
    if ($result === \false) {
        throw ZipException::createFromPhpError();
    }
    return $result;
}
function zip_entry_open($zip_dp, $zip_entry, string $mode = "rb") : void
{
    \error_clear_last();
    $result = \zip_entry_open($zip_dp, $zip_entry, $mode);
    if ($result === \false) {
        throw ZipException::createFromPhpError();
    }
}
function zip_entry_read($zip_entry, int $len = 1024) : string
{
    \error_clear_last();
    $result = \zip_entry_read($zip_entry, $len);
    if ($result === \false) {
        throw ZipException::createFromPhpError();
    }
    return $result;
}
