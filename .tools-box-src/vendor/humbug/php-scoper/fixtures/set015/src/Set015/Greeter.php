<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Set015;

use _HumbugBoxb47773b41c19\Pimple\Container;
class Greeter
{
    public function greet(Container $c) : string
    {
        return $c['hello'];
    }
}
