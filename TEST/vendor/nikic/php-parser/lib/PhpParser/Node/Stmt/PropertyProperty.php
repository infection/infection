<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\Node\Stmt;

use _HumbugBox9658796bb9f0\PhpParser\Node;
class PropertyProperty extends Node\Stmt
{
    public $name;
    public $default;
    public function __construct($name, Node\Expr $default = null, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->name = \is_string($name) ? new Node\VarLikeIdentifier($name) : $name;
        $this->default = $default;
    }
    public function getSubNodeNames() : array
    {
        return ['name', 'default'];
    }
    public function getType() : string
    {
        return 'Stmt_PropertyProperty';
    }
}
