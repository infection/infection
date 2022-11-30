<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection;

interface PseudoType extends Type
{
    public function underlyingType() : Type;
}
