<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\Builder;

use _HumbugBox9658796bb9f0\PhpParser;
use _HumbugBox9658796bb9f0\PhpParser\BuilderHelpers;
use _HumbugBox9658796bb9f0\PhpParser\Node;
class Param implements PhpParser\Builder
{
    protected $name;
    protected $default = null;
    protected $type = null;
    protected $byRef = \false;
    protected $variadic = \false;
    protected $attributeGroups = [];
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    public function setDefault($value)
    {
        $this->default = BuilderHelpers::normalizeValue($value);
        return $this;
    }
    public function setType($type)
    {
        $this->type = BuilderHelpers::normalizeType($type);
        if ($this->type == 'void') {
            throw new \LogicException('Parameter type cannot be void');
        }
        return $this;
    }
    public function setTypeHint($type)
    {
        return $this->setType($type);
    }
    public function makeByRef()
    {
        $this->byRef = \true;
        return $this;
    }
    public function makeVariadic()
    {
        $this->variadic = \true;
        return $this;
    }
    public function addAttribute($attribute)
    {
        $this->attributeGroups[] = BuilderHelpers::normalizeAttribute($attribute);
        return $this;
    }
    public function getNode() : Node
    {
        return new Node\Param(new Node\Expr\Variable($this->name), $this->default, $this->type, $this->byRef, $this->variadic, [], 0, $this->attributeGroups);
    }
}
