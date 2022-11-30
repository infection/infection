<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\Node\Expr;

use _HumbugBox9658796bb9f0\PhpParser\Node\Arg;
use _HumbugBox9658796bb9f0\PhpParser\Node\Expr;
use _HumbugBox9658796bb9f0\PhpParser\Node\Identifier;
use _HumbugBox9658796bb9f0\PhpParser\Node\VariadicPlaceholder;
class MethodCall extends CallLike
{
    public $var;
    public $name;
    public $args;
    public function __construct(Expr $var, $name, array $args = [], array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->var = $var;
        $this->name = \is_string($name) ? new Identifier($name) : $name;
        $this->args = $args;
    }
    public function getSubNodeNames() : array
    {
        return ['var', 'name', 'args'];
    }
    public function getType() : string
    {
        return 'Expr_MethodCall';
    }
    public function getRawArgs() : array
    {
        return $this->args;
    }
}
