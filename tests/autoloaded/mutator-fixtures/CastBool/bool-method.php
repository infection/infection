<?php declare(strict_types=1);

namespace CastBoolBoolMethod;

class Foo
{
    function returnsBool(): bool
    {
        return (bool)preg_match();
    }
}
