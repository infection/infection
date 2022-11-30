<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\UopzException;
function uopz_extend(string $class, string $parent) : void
{
    \error_clear_last();
    $result = \uopz_extend($class, $parent);
    if ($result === \false) {
        throw UopzException::createFromPhpError();
    }
}
function uopz_implement(string $class, string $interface) : void
{
    \error_clear_last();
    $result = \uopz_implement($class, $interface);
    if ($result === \false) {
        throw UopzException::createFromPhpError();
    }
}
