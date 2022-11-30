<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Node\Expr;

use _HumbugBoxb47773b41c19\PhpParser\Node;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr;
use _HumbugBoxb47773b41c19\PhpParser\Node\FunctionLike;
class ArrowFunction extends Expr implements FunctionLike
{
    public $static;
    public $byRef;
    public $params = [];
    public $returnType;
    public $expr;
    public $attrGroups;
    public function __construct(array $subNodes = [], array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->static = $subNodes['static'] ?? \false;
        $this->byRef = $subNodes['byRef'] ?? \false;
        $this->params = $subNodes['params'] ?? [];
        $returnType = $subNodes['returnType'] ?? null;
        $this->returnType = \is_string($returnType) ? new Node\Identifier($returnType) : $returnType;
        $this->expr = $subNodes['expr'];
        $this->attrGroups = $subNodes['attrGroups'] ?? [];
    }
    public function getSubNodeNames() : array
    {
        return ['attrGroups', 'static', 'byRef', 'params', 'returnType', 'expr'];
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
        return [new Node\Stmt\Return_($this->expr)];
    }
    public function getType() : string
    {
        return 'Expr_ArrowFunction';
    }
}
