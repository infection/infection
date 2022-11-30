<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Builder;

use _HumbugBoxb47773b41c19\PhpParser;
use _HumbugBoxb47773b41c19\PhpParser\BuilderHelpers;
use _HumbugBoxb47773b41c19\PhpParser\Node;
use _HumbugBoxb47773b41c19\PhpParser\Node\Identifier;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt;
use _HumbugBoxb47773b41c19\PhpParser\Node\ComplexType;
class Property implements PhpParser\Builder
{
    protected $name;
    protected $flags = 0;
    protected $default = null;
    protected $attributes = [];
    protected $type;
    protected $attributeGroups = [];
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    public function makePublic()
    {
        $this->flags = BuilderHelpers::addModifier($this->flags, Stmt\Class_::MODIFIER_PUBLIC);
        return $this;
    }
    public function makeProtected()
    {
        $this->flags = BuilderHelpers::addModifier($this->flags, Stmt\Class_::MODIFIER_PROTECTED);
        return $this;
    }
    public function makePrivate()
    {
        $this->flags = BuilderHelpers::addModifier($this->flags, Stmt\Class_::MODIFIER_PRIVATE);
        return $this;
    }
    public function makeStatic()
    {
        $this->flags = BuilderHelpers::addModifier($this->flags, Stmt\Class_::MODIFIER_STATIC);
        return $this;
    }
    public function makeReadonly()
    {
        $this->flags = BuilderHelpers::addModifier($this->flags, Stmt\Class_::MODIFIER_READONLY);
        return $this;
    }
    public function setDefault($value)
    {
        $this->default = BuilderHelpers::normalizeValue($value);
        return $this;
    }
    public function setDocComment($docComment)
    {
        $this->attributes = ['comments' => [BuilderHelpers::normalizeDocComment($docComment)]];
        return $this;
    }
    public function setType($type)
    {
        $this->type = BuilderHelpers::normalizeType($type);
        return $this;
    }
    public function addAttribute($attribute)
    {
        $this->attributeGroups[] = BuilderHelpers::normalizeAttribute($attribute);
        return $this;
    }
    public function getNode() : PhpParser\Node
    {
        return new Stmt\Property($this->flags !== 0 ? $this->flags : Stmt\Class_::MODIFIER_PUBLIC, [new Stmt\PropertyProperty($this->name, $this->default)], $this->attributes, $this->type, $this->attributeGroups);
    }
}
