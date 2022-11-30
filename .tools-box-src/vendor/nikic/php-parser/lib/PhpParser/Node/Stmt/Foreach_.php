<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Node\Stmt;

use _HumbugBoxb47773b41c19\PhpParser\Node;
class Foreach_ extends Node\Stmt
{
    public $expr;
    public $keyVar;
    public $byRef;
    public $valueVar;
    public $stmts;
    public function __construct(Node\Expr $expr, Node\Expr $valueVar, array $subNodes = [], array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->expr = $expr;
        $this->keyVar = $subNodes['keyVar'] ?? null;
        $this->byRef = $subNodes['byRef'] ?? \false;
        $this->valueVar = $valueVar;
        $this->stmts = $subNodes['stmts'] ?? [];
    }
    public function getSubNodeNames() : array
    {
        return ['expr', 'keyVar', 'byRef', 'valueVar', 'stmts'];
    }
    public function getType() : string
    {
        return 'Stmt_Foreach';
    }
}
