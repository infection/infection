<?php

namespace _HumbugBoxb47773b41c19\JetBrains\PhpStorm;

use Attribute;
#[Attribute(Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD)]
class Pure
{
    public function __construct(bool $mayDependOnGlobalScope = \false)
    {
    }
}
