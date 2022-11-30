<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\ClassobjException;
function class_alias(string $class, string $alias, bool $autoload = \true) : void
{
    \error_clear_last();
    $safeResult = \class_alias($class, $alias, $autoload);
    if ($safeResult === \false) {
        throw ClassobjException::createFromPhpError();
    }
}
