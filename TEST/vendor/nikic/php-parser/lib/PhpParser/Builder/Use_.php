<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\Builder;

use _HumbugBox9658796bb9f0\PhpParser\Builder;
use _HumbugBox9658796bb9f0\PhpParser\BuilderHelpers;
use _HumbugBox9658796bb9f0\PhpParser\Node;
use _HumbugBox9658796bb9f0\PhpParser\Node\Stmt;
class Use_ implements Builder
{
    protected $name;
    protected $type;
    protected $alias = null;
    public function __construct($name, int $type)
    {
        $this->name = BuilderHelpers::normalizeName($name);
        $this->type = $type;
    }
    public function as(string $alias)
    {
        $this->alias = $alias;
        return $this;
    }
    public function getNode() : Node
    {
        return new Stmt\Use_([new Stmt\UseUse($this->name, $this->alias)], $this->type);
    }
}
