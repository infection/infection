<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type;

use _HumbugBoxb47773b41c19\Fidry\Console\InputAssert;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
/**
@implements
*/
final class StringChoiceType implements ScalarType
{
    private array $choices;
    public function __construct(array $choices)
    {
        $this->choices = $choices;
    }
    public function coerceValue($value, string $label) : string
    {
        $value = (new StringType())->coerceValue($value, $label);
        /**
        @psalm-suppress */
        InputAssert::castThrowException(fn() => Assert::inArray($value, $this->choices), $label);
        return $value;
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
