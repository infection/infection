<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type;

use _HumbugBoxb47773b41c19\Fidry\Console\InputAssert;
/**
@implements
*/
final class FloatType implements ScalarType
{
    public function coerceValue($value, string $label) : float
    {
        InputAssert::numericString($value, $label);
        return (float) $value;
    }
    public function getTypeClassNames() : array
    {
        return [self::class];
    }
    public function getPsalmTypeDeclaration() : string
    {
        return 'float';
    }
    public function getPhpTypeDeclaration() : ?string
    {
        return 'float';
    }
}
