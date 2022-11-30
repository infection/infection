<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoTypes;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoType;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Type;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Array_;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Integer;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Mixed_;
/**
@psalm-immutable
*/
final class List_ extends Array_ implements PseudoType
{
    public function underlyingType() : Type
    {
        return new Array_();
    }
    public function __construct(?Type $valueType = null)
    {
        parent::__construct($valueType, new Integer());
    }
    public function __toString() : string
    {
        if ($this->valueType instanceof Mixed_) {
            return 'list';
        }
        return 'list<' . $this->valueType . '>';
    }
}
