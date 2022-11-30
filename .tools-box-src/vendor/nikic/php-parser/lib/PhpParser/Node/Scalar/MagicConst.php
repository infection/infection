<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Node\Scalar;

use _HumbugBoxb47773b41c19\PhpParser\Node\Scalar;
abstract class MagicConst extends Scalar
{
    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }
    public function getSubNodeNames() : array
    {
        return [];
    }
    public abstract function getName() : string;
}
