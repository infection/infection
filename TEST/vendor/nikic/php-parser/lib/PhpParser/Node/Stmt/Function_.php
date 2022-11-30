<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\Node\Stmt;

use _HumbugBox9658796bb9f0\PhpParser\Node;
use _HumbugBox9658796bb9f0\PhpParser\Node\FunctionLike;
class Function_ extends Node\Stmt implements FunctionLike
{
    public $byRef;
    public $name;
    public $params;
    public $returnType;
    public $stmts;
    public $attrGroups;
    public $namespacedName;
    public function __construct($name, array $subNodes = [], array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->byRef = $subNodes['byRef'] ?? \false;
        $this->name = \is_string($name) ? new Node\Identifier($name) : $name;
        $this->params = $subNodes['params'] ?? [];
        $returnType = $subNodes['returnType'] ?? null;
        $this->returnType = \is_string($returnType) ? new Node\Identifier($returnType) : $returnType;
        $this->stmts = $subNodes['stmts'] ?? [];
        $this->attrGroups = $subNodes['attrGroups'] ?? [];
    }
    public function getSubNodeNames() : array
    {
        return ['attrGroups', 'byRef', 'name', 'params', 'returnType', 'stmts'];
    }
    public function returnsByRef() : bool
    {
        return $this->byRef;
    }
    public function getParams() : array
    {
        return $this->params;
    }
    public function getReturnType()
    {
        return $this->returnType;
    }
    public function getAttrGroups() : array
    {
        return $this->attrGroups;
    }
    public function getStmts() : array
    {
        return $this->stmts;
    }
    public function getType() : string
    {
        return 'Stmt_Function';
    }
}
