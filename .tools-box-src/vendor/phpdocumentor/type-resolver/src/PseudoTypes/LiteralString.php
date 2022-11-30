<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoTypes;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoType;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Type;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\String_;
/**
@psalm-immutable
*/
final class LiteralString extends String_ implements PseudoType
{
    public function underlyingType() : Type
    {
        return new String_();
    }
    public function __toString() : string
    {
        return 'literal-string';
    }
}
