<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Type;
/**
@psalm-immutable
*/
final class Nullable implements Type
{
    private Type $realType;
    public function __construct(Type $realType)
    {
        $this->realType = $realType;
    }
    public function getActualType() : Type
    {
        return $this->realType;
    }
    public function __toString() : string
    {
        return '?' . $this->realType->__toString();
    }
}
