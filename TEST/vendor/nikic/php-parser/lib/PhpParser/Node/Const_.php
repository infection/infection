<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\Node;

use _HumbugBox9658796bb9f0\PhpParser\NodeAbstract;
class Const_ extends NodeAbstract
{
    public $name;
    public $value;
    public $namespacedName;
    public function __construct($name, Expr $value, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->name = \is_string($name) ? new Identifier($name) : $name;
        $this->value = $value;
    }
    public function getSubNodeNames() : array
    {
        return ['name', 'value'];
    }
    public function getType() : string
    {
        return 'Const';
    }
}
