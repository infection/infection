<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Builder;

use _HumbugBoxb47773b41c19\PhpParser;
use _HumbugBoxb47773b41c19\PhpParser\BuilderHelpers;
use _HumbugBoxb47773b41c19\PhpParser\Node;
use _HumbugBoxb47773b41c19\PhpParser\Node\Identifier;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt;
class EnumCase implements PhpParser\Builder
{
    protected $name;
    protected $value = null;
    protected $attributes = [];
    protected $attributeGroups = [];
    public function __construct($name)
    {
        $this->name = $name;
    }
    public function setValue($value)
    {
        $this->value = BuilderHelpers::normalizeValue($value);
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
        return new Stmt\EnumCase($this->name, $this->value, $this->attributes, $this->attributeGroups);
    }
}
