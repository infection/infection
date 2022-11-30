<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\FpmException;
function fastcgi_finish_request() : void
{
    \error_clear_last();
    $result = \fastcgi_finish_request();
    if ($result === \false) {
        throw FpmException::createFromPhpError();
    }
}
