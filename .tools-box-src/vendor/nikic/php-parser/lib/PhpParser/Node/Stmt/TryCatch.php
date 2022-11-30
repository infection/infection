<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Node\Stmt;

use _HumbugBoxb47773b41c19\PhpParser\Node;
class TryCatch extends Node\Stmt
{
    public $stmts;
    public $catches;
    public $finally;
    public function __construct(array $stmts, array $catches, Finally_ $finally = null, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->stmts = $stmts;
        $this->catches = $catches;
        $this->finally = $finally;
    }
    public function getSubNodeNames() : array
    {
        return ['stmts', 'catches', 'finally'];
    }
    public function getType() : string
    {
        return 'Stmt_TryCatch';
    }
}
