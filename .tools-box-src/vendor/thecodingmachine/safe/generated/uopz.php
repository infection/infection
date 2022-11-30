<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\UopzException;
function uopz_extend(string $class, string $parent) : void
{
    \error_clear_last();
    $safeResult = \uopz_extend($class, $parent);
    if ($safeResult === \false) {
        throw UopzException::createFromPhpError();
    }
}
function uopz_implement(string $class, string $interface) : void
{
    \error_clear_last();
    $safeResult = \uopz_implement($class, $interface);
    if ($safeResult === \false) {
        throw UopzException::createFromPhpError();
    }
}
