<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\Builder;

use _HumbugBox9658796bb9f0\PhpParser;
use _HumbugBox9658796bb9f0\PhpParser\BuilderHelpers;
use _HumbugBox9658796bb9f0\PhpParser\Node;
use _HumbugBox9658796bb9f0\PhpParser\Node\Identifier;
use _HumbugBox9658796bb9f0\PhpParser\Node\Name;
use _HumbugBox9658796bb9f0\PhpParser\Node\Stmt;
class Enum_ extends Declaration
{
    protected $name;
    protected $scalarType = null;
    protected $implements = [];
    protected $uses = [];
    protected $enumCases = [];
    protected $constants = [];
    protected $methods = [];
    protected $attributeGroups = [];
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    public function setScalarType($scalarType)
    {
        $this->scalarType = BuilderHelpers::normalizeType($scalarType);
        return $this;
    }
    public function implement(...$interfaces)
    {
        foreach ($interfaces as $interface) {
            $this->implements[] = BuilderHelpers::normalizeName($interface);
        }
        return $this;
    }
    public function addStmt($stmt)
    {
        $stmt = BuilderHelpers::normalizeNode($stmt);
        $targets = [Stmt\TraitUse::class => &$this->uses, Stmt\EnumCase::class => &$this->enumCases, Stmt\ClassConst::class => &$this->constants, Stmt\ClassMethod::class => &$this->methods];
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
        return new Stmt\Enum_($this->name, ['scalarType' => $this->scalarType, 'implements' => $this->implements, 'stmts' => \array_merge($this->uses, $this->enumCases, $this->constants, $this->methods), 'attrGroups' => $this->attributeGroups], $this->attributes);
    }
}
