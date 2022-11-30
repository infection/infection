<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\VarException;
function settype(&$var, string $type) : void
{
    \error_clear_last();
    $result = \settype($var, $type);
    if ($result === \false) {
        throw VarException::createFromPhpError();
    }
}
