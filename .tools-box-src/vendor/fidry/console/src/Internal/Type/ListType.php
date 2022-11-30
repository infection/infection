<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type;

use _HumbugBoxb47773b41c19\Fidry\Console\InputAssert;
use function array_map;
use function sprintf;
/**
@template
@implements
*/
final class ListType implements InputType
{
    private InputType $innerType;
    public function __construct(InputType $innerType)
    {
        $this->innerType = $innerType;
    }
    public function coerceValue($value, string $label) : array
    {
        InputAssert::assertIsList($value, $label);
        return array_map(fn(string $element) => $this->innerType->coerceValue($element, $label), $value);
    }
    public function getTypeClassNames() : array
    {
        return [self::class, ...$this->innerType->getTypeClassNames()];
    }
    /**
    @psalm-suppress */
    public function getPsalmTypeDeclaration() : string
    {
        /**
        @psalm-suppress */
        return sprintf('list<%s>', $this->innerType->getPsalmTypeDeclaration());
    }
    public function getPhpTypeDeclaration() : ?string
    {
        return 'array';
    }
}
