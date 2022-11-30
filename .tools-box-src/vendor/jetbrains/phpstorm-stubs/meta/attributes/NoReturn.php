<?php

namespace _HumbugBoxb47773b41c19\JetBrains\PhpStorm;

use Attribute;
#[Attribute(Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD)]
class NoReturn
{
    public const ANY_ARGUMENT = 1;
    public function __construct(...$arguments)
    {
    }
}
