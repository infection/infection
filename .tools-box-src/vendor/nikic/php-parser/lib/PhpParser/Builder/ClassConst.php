<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Builder;

use _HumbugBoxb47773b41c19\PhpParser;
use _HumbugBoxb47773b41c19\PhpParser\BuilderHelpers;
use _HumbugBoxb47773b41c19\PhpParser\Node;
use _HumbugBoxb47773b41c19\PhpParser\Node\Const_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Identifier;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt;
class ClassConst implements PhpParser\Builder
{
    protected $flags = 0;
    protected $attributes = [];
    protected $constants = [];
    protected $attributeGroups = [];
    public function __construct($name, $value)
    {
        $this->constants = [new Const_($name, BuilderHelpers::normalizeValue($value))];
    }
    public function addConst($name, $value)
    {
        $this->constants[] = new Const_($name, BuilderHelpers::normalizeValue($value));
        return $this;
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
    public function makeFinal()
    {
        $this->flags = BuilderHelpers::addModifier($this->flags, Stmt\Class_::MODIFIER_FINAL);
        return $this;
    }
    public function setDocComment($docComment)
    {
        $this->attributes = ['comments' => [BuilderHelpers::normalizeDocComment($docComment)]];
        return $this;
    }
    public function addAttribute($attribute)
    {
        $this->attributeGroups[] = BuilderHelpers::normalizeAttribute($attribute);
        return $this;
    }
    public function getNode() : PhpParser\Node
    {
        return new Stmt\ClassConst($this->constants, $this->flags, $this->attributes, $this->attributeGroups);
    }
}
