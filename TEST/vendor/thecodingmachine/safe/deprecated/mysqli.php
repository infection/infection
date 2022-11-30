<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\MysqliException;
function mysqli_get_client_stats() : array
{
    \error_clear_last();
    $result = \mysqli_get_client_stats();
    if ($result === \false) {
        throw MysqliException::createFromPhpError();
    }
    return $result;
}
