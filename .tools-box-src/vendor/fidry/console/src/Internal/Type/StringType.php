<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type;

use _HumbugBoxb47773b41c19\Fidry\Console\InputAssert;
use function trim;
/**
@implements
*/
final class StringType implements ScalarType
{
    public function coerceValue($value, string $label) : string
    {
        InputAssert::string($value, $label);
        return trim($value);
    }
    public function getTypeClassNames() : array
    {
        return [self::class];
    }
    public function getPsalmTypeDeclaration() : string
    {
        return 'string';
    }
    public function getPhpTypeDeclaration() : ?string
    {
        return 'string';
    }
}
