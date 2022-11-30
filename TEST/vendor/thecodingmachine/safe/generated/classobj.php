<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\ClassobjException;
function class_alias(string $class, string $alias, bool $autoload = \true) : void
{
    \error_clear_last();
    $result = \class_alias($class, $alias, $autoload);
    if ($result === \false) {
        throw ClassobjException::createFromPhpError();
    }
}
