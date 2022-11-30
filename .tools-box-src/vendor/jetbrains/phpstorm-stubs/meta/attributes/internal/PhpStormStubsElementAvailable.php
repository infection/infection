<?php

namespace _HumbugBoxb47773b41c19\JetBrains\PhpStorm\Internal;

use Attribute;
use _HumbugBoxb47773b41c19\JetBrains\PhpStorm\Deprecated;
use _HumbugBoxb47773b41c19\JetBrains\PhpStorm\ExpectedValues;
#[Attribute(Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER)]
class PhpStormStubsElementAvailable
{
    public function __construct(#[ExpectedValues(Deprecated::PHP_VERSIONS)] $from, #[ExpectedValues(Deprecated::PHP_VERSIONS)] $to = null)
    {
    }
}
