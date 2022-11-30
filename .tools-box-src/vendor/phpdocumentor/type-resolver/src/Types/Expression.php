<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Type;
/**
@psalm-immutable
*/
final class Expression implements Type
{
    protected Type $valueType;
    public function __construct(Type $valueType)
    {
        $this->valueType = $valueType;
    }
    public function getValueType() : Type
    {
        return $this->valueType;
    }
    public function __toString() : string
    {
        return '(' . $this->valueType . ')';
    }
}
