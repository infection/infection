<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\FileinfoException;
function finfo_close($finfo) : void
{
    \error_clear_last();
    $result = \finfo_close($finfo);
    if ($result === \false) {
        throw FileinfoException::createFromPhpError();
    }
}
function finfo_open(int $flags = \FILEINFO_NONE, string $magic_database = null)
{
    \error_clear_last();
    if ($magic_database !== null) {
        $result = \finfo_open($flags, $magic_database);
    } else {
        $result = \finfo_open($flags);
    }
    if ($result === \false) {
        throw FileinfoException::createFromPhpError();
    }
    return $result;
}
function mime_content_type($filename) : string
{
    \error_clear_last();
    $result = \mime_content_type($filename);
    if ($result === \false) {
        throw FileinfoException::createFromPhpError();
    }
    return $result;
}
