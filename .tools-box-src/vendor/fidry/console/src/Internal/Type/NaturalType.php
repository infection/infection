<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type;

use _HumbugBoxb47773b41c19\Fidry\Console\InputAssert;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
/**
@implements
*/
final class NaturalType implements ScalarType
{
    public function coerceValue($value, string $label) : int
    {
        InputAssert::integerString($value, $label);
        $intValue = (int) $value;
        /**
        @psalm-suppress */
        InputAssert::castThrowException(static fn() => Assert::natural($intValue), $label);
        return (int) $value;
    }
    public function getTypeClassNames() : array
    {
        return [self::class];
    }
    public function getPsalmTypeDeclaration() : string
    {
        return 'positive-int|0';
    }
    public function getPhpTypeDeclaration() : ?string
    {
        return 'int';
    }
}
