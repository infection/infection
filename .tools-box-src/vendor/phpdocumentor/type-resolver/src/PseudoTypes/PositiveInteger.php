<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoTypes;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoType;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Type;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Integer;
/**
@psalm-immutable
*/
final class PositiveInteger extends Integer implements PseudoType
{
    public function underlyingType() : Type
    {
        return new Integer();
    }
    public function __toString() : string
    {
        return 'positive-int';
    }
}
