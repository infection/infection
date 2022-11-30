<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\Node\Expr;

use _HumbugBox9658796bb9f0\PhpParser\Node\Expr;
class ArrayDimFetch extends Expr
{
    public $var;
    public $dim;
    public function __construct(Expr $var, Expr $dim = null, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->var = $var;
        $this->dim = $dim;
    }
    public function getSubNodeNames() : array
    {
        return ['var', 'dim'];
    }
    public function getType() : string
    {
        return 'Expr_ArrayDimFetch';
    }
}
