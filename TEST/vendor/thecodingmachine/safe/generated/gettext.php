<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\GettextException;
function bindtextdomain(string $domain, string $directory) : string
{
    \error_clear_last();
    $result = \bindtextdomain($domain, $directory);
    if ($result === \false) {
        throw GettextException::createFromPhpError();
    }
    return $result;
}
