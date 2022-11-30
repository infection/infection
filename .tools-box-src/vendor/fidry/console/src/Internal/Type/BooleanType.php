<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type;

use _HumbugBoxb47773b41c19\Fidry\Console\InputAssert;
/**
@implements
*/
final class BooleanType implements ScalarType
{
    public function coerceValue($value, string $label) : bool
    {
        InputAssert::assertIsScalar($value, $label);
        return (bool) $value;
    }
    public function getTypeClassNames() : array
    {
        return [self::class];
    }
    public function getPsalmTypeDeclaration() : string
    {
        return 'bool';
    }
    public function getPhpTypeDeclaration() : ?string
    {
        return 'bool';
    }
}
