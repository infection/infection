<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type;

use _HumbugBoxb47773b41c19\Fidry\Console\InputAssert;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
/**
@implements
*/
final class NaturalRangeType implements InputType
{
    private int $min;
    private int $max;
    public function __construct(int $min, int $max)
    {
        if ($min < $max) {
            $this->min = $min;
            $this->max = $max;
        } else {
            $this->min = $max;
            $this->max = $min;
        }
    }
    public function coerceValue($value, string $label) : int
    {
        $intValue = (new NaturalType())->coerceValue($value, $label);
        /**
        @psalm-suppress */
        InputAssert::castThrowException(fn() => Assert::range($intValue, $this->min, $this->max), $label);
        return $intValue;
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
