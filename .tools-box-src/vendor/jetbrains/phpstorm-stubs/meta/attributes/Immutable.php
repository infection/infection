<?php

namespace _HumbugBoxb47773b41c19\JetBrains\PhpStorm;

use Attribute;
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_CLASS)]
class Immutable
{
    public const CONSTRUCTOR_WRITE_SCOPE = "constructor";
    public const PRIVATE_WRITE_SCOPE = "private";
    public const PROTECTED_WRITE_SCOPE = "protected";
    public function __construct(#[ExpectedValues(valuesFromClass: Immutable::class)] $allowedWriteScope = self::CONSTRUCTOR_WRITE_SCOPE)
    {
    }
}
