<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type;

use _HumbugBoxb47773b41c19\Fidry\Console\InputAssert;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
/**
@implements
*/
final class PositiveIntegerType implements ScalarType
{
    public function coerceValue($value, string $label) : int
    {
        $intValue = (new NaturalType())->coerceValue($value, $label);
        /**
        @psalm-suppress */
        InputAssert::castThrowException(static fn() => Assert::positiveInteger($intValue), $label);
        return $intValue;
    }
    public function getTypeClassNames() : array
    {
        return [self::class];
    }
    public function getPsalmTypeDeclaration() : string
    {
        return 'positive-int';
    }
    public function getPhpTypeDeclaration() : ?string
    {
        return 'int';
    }
}
