<?php

namespace _HumbugBoxb47773b41c19\JetBrains\PhpStorm;

use Attribute;
#[Attribute(Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::TARGET_CLASS_CONSTANT | Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Deprecated
{
    public const PHP_VERSIONS = ["5.3", "5.4", "5.5", "5.6", "7.0", "7.1", "7.2", "7.3", "7.4", "8.0", "8.1", "8.2"];
    public function __construct($reason = "", $replacement = "", #[ExpectedValues(self::PHP_VERSIONS)] $since = "5.6")
    {
    }
}
