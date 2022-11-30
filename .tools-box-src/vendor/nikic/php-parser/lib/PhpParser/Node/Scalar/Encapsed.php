<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Node\Scalar;

use _HumbugBoxb47773b41c19\PhpParser\Node\Expr;
use _HumbugBoxb47773b41c19\PhpParser\Node\Scalar;
class Encapsed extends Scalar
{
    public $parts;
    public function __construct(array $parts, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->parts = $parts;
    }
    public function getSubNodeNames() : array
    {
        return ['parts'];
    }
    public function getType() : string
    {
        return 'Scalar_Encapsed';
    }
}
