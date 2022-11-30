<?php

namespace _HumbugBoxb47773b41c19\JetBrains\PhpStorm\Internal;

use Attribute;
#[Attribute(Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class LanguageLevelTypeAware
{
    public function __construct(array $languageLevelTypeMap, string $default)
    {
    }
}
