<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\ZipException;
function zip_entry_close($zip_entry) : void
{
    \error_clear_last();
    $safeResult = \zip_entry_close($zip_entry);
    if ($safeResult === \false) {
        throw ZipException::createFromPhpError();
    }
}
function zip_entry_compressedsize($zip_entry) : int
{
    \error_clear_last();
    $safeResult = \zip_entry_compressedsize($zip_entry);
    if ($safeResult === \false) {
        throw ZipException::createFromPhpError();
    }
    return $safeResult;
}
function zip_entry_compressionmethod($zip_entry) : string
{
    \error_clear_last();
    $safeResult = \zip_entry_compressionmethod($zip_entry);
    if ($safeResult === \false) {
        throw ZipException::createFromPhpError();
    }
    return $safeResult;
}
function zip_entry_filesize($zip_entry) : int
{
    \error_clear_last();
    $safeResult = \zip_entry_filesize($zip_entry);
    if ($safeResult === \false) {
        throw ZipException::createFromPhpError();
    }
    return $safeResult;
}
function zip_entry_name($zip_entry) : string
{
    \error_clear_last();
    $safeResult = \zip_entry_name($zip_entry);
    if ($safeResult === \false) {
        throw ZipException::createFromPhpError();
    }
    return $safeResult;
}
function zip_entry_open($zip_dp, $zip_entry, string $mode = "rb") : void
{
    \error_clear_last();
    $safeResult = \zip_entry_open($zip_dp, $zip_entry, $mode);
    if ($safeResult === \false) {
        throw ZipException::createFromPhpError();
    }
}
function zip_entry_read($zip_entry, int $len = 1024) : string
{
    \error_clear_last();
    $safeResult = \zip_entry_read($zip_entry, $len);
    if ($safeResult === \false) {
        throw ZipException::createFromPhpError();
    }
    return $safeResult;
}
