<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Node;

use _HumbugBoxb47773b41c19\PhpParser\Node;
use _HumbugBoxb47773b41c19\PhpParser\NodeAbstract;
class Attribute extends NodeAbstract
{
    public $name;
    public $args;
    public function __construct(Name $name, array $args = [], array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->name = $name;
        $this->args = $args;
    }
    public function getSubNodeNames() : array
    {
        return ['name', 'args'];
    }
    public function getType() : string
    {
        return 'Attribute';
    }
}
