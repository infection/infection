<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\Node\Stmt;

use _HumbugBox9658796bb9f0\PhpParser\Node;
use _HumbugBox9658796bb9f0\PhpParser\Node\Expr;
class StaticVar extends Node\Stmt
{
    public $var;
    public $default;
    public function __construct(Expr\Variable $var, Node\Expr $default = null, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->var = $var;
        $this->default = $default;
    }
    public function getSubNodeNames() : array
    {
        return ['var', 'default'];
    }
    public function getType() : string
    {
        return 'Stmt_StaticVar';
    }
}
