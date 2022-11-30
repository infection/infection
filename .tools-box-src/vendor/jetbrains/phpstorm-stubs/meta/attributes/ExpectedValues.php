<?php

namespace _HumbugBoxb47773b41c19\JetBrains\PhpStorm;

use Attribute;
#[Attribute(Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class ExpectedValues
{
    public function __construct(array $values = [], array $flags = [], string $valuesFromClass = null, string $flagsFromClass = null)
    {
    }
}
