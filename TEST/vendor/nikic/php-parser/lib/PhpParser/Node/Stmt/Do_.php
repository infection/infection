<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\Node\Stmt;

use _HumbugBox9658796bb9f0\PhpParser\Node;
class Do_ extends Node\Stmt
{
    public $stmts;
    public $cond;
    public function __construct(Node\Expr $cond, array $stmts = [], array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->cond = $cond;
        $this->stmts = $stmts;
    }
    public function getSubNodeNames() : array
    {
        return ['stmts', 'cond'];
    }
    public function getType() : string
    {
        return 'Stmt_Do';
    }
}
