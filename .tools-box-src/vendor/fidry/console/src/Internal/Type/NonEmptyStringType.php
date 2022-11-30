<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type;

use _HumbugBoxb47773b41c19\Fidry\Console\Input\InvalidInputValueType;
use _HumbugBoxb47773b41c19\Fidry\Console\InputAssert;
use function sprintf;
use function trim;
/**
@implements
*/
final class NonEmptyStringType implements ScalarType
{
    public function coerceValue($value, string $label) : string
    {
        InputAssert::string($value, $label);
        $trimmedValue = trim($value);
        if ('' === $trimmedValue) {
            throw new InvalidInputValueType(sprintf('Expected a non-empty string for %s.', $label));
        }
        return $trimmedValue;
    }
    public function getTypeClassNames() : array
    {
        return [self::class];
    }
    public function getPsalmTypeDeclaration() : string
    {
        return 'non-empty-string';
    }
    public function getPhpTypeDeclaration() : ?string
    {
        return 'string';
    }
}
