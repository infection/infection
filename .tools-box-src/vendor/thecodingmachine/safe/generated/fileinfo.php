<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\FileinfoException;
function finfo_close($finfo) : void
{
    \error_clear_last();
    $safeResult = \finfo_close($finfo);
    if ($safeResult === \false) {
        throw FileinfoException::createFromPhpError();
    }
}
function finfo_open(int $flags = \FILEINFO_NONE, string $magic_database = null)
{
    \error_clear_last();
    if ($magic_database !== null) {
        $safeResult = \finfo_open($flags, $magic_database);
    } else {
        $safeResult = \finfo_open($flags);
    }
    if ($safeResult === \false) {
        throw FileinfoException::createFromPhpError();
    }
    return $safeResult;
}
function mime_content_type($filename) : string
{
    \error_clear_last();
    $safeResult = \mime_content_type($filename);
    if ($safeResult === \false) {
        throw FileinfoException::createFromPhpError();
    }
    return $safeResult;
}
