<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoTypes;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoType;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Type;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\AggregatedType;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Compound;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Float_;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Integer;
/**
@psalm-immutable
*/
final class Numeric_ extends AggregatedType implements PseudoType
{
    public function __construct()
    {
        AggregatedType::__construct([new NumericString(), new Integer(), new Float_()], '|');
    }
    public function underlyingType() : Type
    {
        return new Compound([new NumericString(), new Integer(), new Float_()]);
    }
    public function __toString() : string
    {
        return 'numeric';
    }
}
