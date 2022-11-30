<?php

namespace _HumbugBoxb47773b41c19\Composer\Pcre;

class UnexpectedNullMatchException extends PcreException
{
    public static function fromFunction($function, $pattern)
    {
        throw new \LogicException('fromFunction should not be called on ' . self::class . ', use ' . PcreException::class);
    }
}
