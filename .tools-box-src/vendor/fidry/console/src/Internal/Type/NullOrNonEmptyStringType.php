<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type;

/**
@implements
*/
final class NullOrNonEmptyStringType implements ScalarType
{
    public function coerceValue($value, string $label) : ?string
    {
        $trimmedValue = (new StringType())->coerceValue($value, $label);
        /**
        @psalm-suppress */
        return '' === $trimmedValue ? null : $trimmedValue;
    }
    public function getTypeClassNames() : array
    {
        return [self::class];
    }
    public function getPsalmTypeDeclaration() : string
    {
        return 'null|non-empty-string';
    }
    public function getPhpTypeDeclaration() : ?string
    {
        return '?string';
    }
}
