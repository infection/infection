<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\GettextException;
function bindtextdomain(string $domain, string $directory) : string
{
    \error_clear_last();
    $safeResult = \bindtextdomain($domain, $directory);
    if ($safeResult === \false) {
        throw GettextException::createFromPhpError();
    }
    return $safeResult;
}
