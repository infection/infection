<?php

namespace CastBoolBoolMethod;

class Foo
{
    function returnsBool(): bool
    {
        return (bool)preg_match();
    }
}
