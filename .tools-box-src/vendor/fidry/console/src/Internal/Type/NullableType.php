<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type;

/**
@template
@implements
*/
final class NullableType implements InputType
{
    private InputType $innerType;
    public function __construct(InputType $innerType)
    {
        $this->innerType = $innerType;
    }
    public function coerceValue($value, string $label)
    {
        return null === $value ? $value : $this->innerType->coerceValue($value, $label);
    }
    public function getTypeClassNames() : array
    {
        return [self::class, ...$this->innerType->getTypeClassNames()];
    }
    public function getPsalmTypeDeclaration() : string
    {
        return 'null|' . $this->innerType->getPsalmTypeDeclaration();
    }
    public function getPhpTypeDeclaration() : ?string
    {
        $innerPhpTypeDeclaration = $this->innerType->getPhpTypeDeclaration();
        return null === $innerPhpTypeDeclaration ? null : '?' . $innerPhpTypeDeclaration;
    }
}
