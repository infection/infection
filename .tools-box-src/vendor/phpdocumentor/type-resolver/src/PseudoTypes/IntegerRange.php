<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoTypes;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoType;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Type;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Integer;
/**
@psalm-immutable
*/
final class IntegerRange extends Integer implements PseudoType
{
    private string $minValue;
    private string $maxValue;
    public function __construct(string $minValue, string $maxValue)
    {
        $this->minValue = $minValue;
        $this->maxValue = $maxValue;
    }
    public function underlyingType() : Type
    {
        return new Integer();
    }
    public function getMinValue() : string
    {
        return $this->minValue;
    }
    public function getMaxValue() : string
    {
        return $this->maxValue;
    }
    public function __toString() : string
    {
        return 'int<' . $this->minValue . ', ' . $this->maxValue . '>';
    }
}
