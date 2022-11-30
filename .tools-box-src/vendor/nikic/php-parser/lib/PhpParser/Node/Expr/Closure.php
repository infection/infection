<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Node\Expr;

use _HumbugBoxb47773b41c19\PhpParser\Node;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr;
use _HumbugBoxb47773b41c19\PhpParser\Node\FunctionLike;
class Closure extends Expr implements FunctionLike
{
    public $static;
    public $byRef;
    public $params;
    public $uses;
    public $returnType;
    public $stmts;
    public $attrGroups;
    public function __construct(array $subNodes = [], array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->static = $subNodes['static'] ?? \false;
        $this->byRef = $subNodes['byRef'] ?? \false;
        $this->params = $subNodes['params'] ?? [];
        $this->uses = $subNodes['uses'] ?? [];
        $returnType = $subNodes['returnType'] ?? null;
        $this->returnType = \is_string($returnType) ? new Node\Identifier($returnType) : $returnType;
        $this->stmts = $subNodes['stmts'] ?? [];
        $this->attrGroups = $subNodes['attrGroups'] ?? [];
    }
    public function getSubNodeNames() : array
    {
        return ['attrGroups', 'static', 'byRef', 'params', 'uses', 'returnType', 'stmts'];
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
    public function getStmts() : array
    {
        return $this->stmts;
    }
    public function getAttrGroups() : array
    {
        return $this->attrGroups;
    }
    public function getType() : string
    {
        return 'Expr_Closure';
    }
}
