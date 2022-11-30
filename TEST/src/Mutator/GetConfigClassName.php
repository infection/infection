<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator;

trait GetConfigClassName
{
    public static function getConfigClassName() : string
    {
        return self::class . 'Config';
    }
}
