<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoType;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Type;
/**
@psalm-immutable
*/
final class ArrayKey extends AggregatedType implements PseudoType
{
    public function __construct()
    {
        parent::__construct([new String_(), new Integer()], '|');
    }
    public function underlyingType() : Type
    {
        return new Compound([new String_(), new Integer()]);
    }
    public function __toString() : string
    {
        return 'array-key';
    }
}
