<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\Node;

use _HumbugBox9658796bb9f0\PhpParser\NodeAbstract;
class VariadicPlaceholder extends NodeAbstract
{
    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }
    public function getType() : string
    {
        return 'VariadicPlaceholder';
    }
    public function getSubNodeNames() : array
    {
        return [];
    }
}
