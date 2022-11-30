<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\Node;

class NullableType extends ComplexType
{
    public $type;
    public function __construct($type, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->type = \is_string($type) ? new Identifier($type) : $type;
    }
    public function getSubNodeNames() : array
    {
        return ['type'];
    }
    public function getType() : string
    {
        return 'NullableType';
    }
}
