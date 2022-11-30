<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19;

function foo() : bool
{
    return \true;
}
if (!\function_exists('_HumbugBoxb47773b41c19\\bar')) {
    function bar() : bool
    {
        return \true;
    }
}
if (\function_exists('_HumbugBoxb47773b41c19\\baz')) {
    baz();
}
