<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\Builder;

use _HumbugBox9658796bb9f0\PhpParser;
use _HumbugBox9658796bb9f0\PhpParser\BuilderHelpers;
use _HumbugBox9658796bb9f0\PhpParser\Node;
use _HumbugBox9658796bb9f0\PhpParser\Node\Name;
use _HumbugBox9658796bb9f0\PhpParser\Node\Stmt;
class Class_ extends Declaration
{
    protected $name;
    protected $extends = null;
    protected $implements = [];
    protected $flags = 0;
    protected $uses = [];
    protected $constants = [];
    protected $properties = [];
    protected $methods = [];
    protected $attributeGroups = [];
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    public function extend($class)
    {
        $this->extends = BuilderHelpers::normalizeName($class);
        return $this;
    }
    public function implement(...$interfaces)
    {
        foreach ($interfaces as $interface) {
            $this->implements[] = BuilderHelpers::normalizeName($interface);
        }
        return $this;
    }
    public function makeAbstract()
    {
        $this->flags = BuilderHelpers::addClassModifier($this->flags, Stmt\Class_::MODIFIER_ABSTRACT);
        return $this;
    }
    public function makeFinal()
    {
        $this->flags = BuilderHelpers::addClassModifier($this->flags, Stmt\Class_::MODIFIER_FINAL);
        return $this;
    }
    public function makeReadonly()
    {
        $this->flags = BuilderHelpers::addClassModifier($this->flags, Stmt\Class_::MODIFIER_READONLY);
        return $this;
    }
    public function addStmt($stmt)
    {
        $stmt = BuilderHelpers::normalizeNode($stmt);
        $targets = [Stmt\TraitUse::class => &$this->uses, Stmt\ClassConst::class => &$this->constants, Stmt\Property::class => &$this->properties, Stmt\ClassMethod::class => &$this->methods];
        $class = \get_class($stmt);
        if (!isset($targets[$class])) {
            throw new \LogicException(\sprintf('Unexpected node of type "%s"', $stmt->getType()));
        }
        $targets[$class][] = $stmt;
        return $this;
    }
    public function addAttribute($attribute)
    {
        $this->attributeGroups[] = BuilderHelpers::normalizeAttribute($attribute);
        return $this;
    }
    public function getNode() : PhpParser\Node
    {
        return new Stmt\Class_($this->name, ['flags' => $this->flags, 'extends' => $this->extends, 'implements' => $this->implements, 'stmts' => \array_merge($this->uses, $this->constants, $this->properties, $this->methods), 'attrGroups' => $this->attributeGroups], $this->attributes);
    }
}
