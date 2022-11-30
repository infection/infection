<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\MysqliException;
function mysqli_get_client_stats() : array
{
    \error_clear_last();
    $result = \mysqli_get_client_stats();
    if ($result === \false) {
        throw MysqliException::createFromPhpError();
    }
    return $result;
}
