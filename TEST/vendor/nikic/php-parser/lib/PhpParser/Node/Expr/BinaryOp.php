<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\Node\Expr;

use _HumbugBox9658796bb9f0\PhpParser\Node\Expr;
abstract class BinaryOp extends Expr
{
    public $left;
    public $right;
    public function __construct(Expr $left, Expr $right, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->left = $left;
        $this->right = $right;
    }
    public function getSubNodeNames() : array
    {
        return ['left', 'right'];
    }
    public abstract function getOperatorSigil() : string;
}
