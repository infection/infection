<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Node\Stmt;

use _HumbugBoxb47773b41c19\PhpParser\Node;
class For_ extends Node\Stmt
{
    public $init;
    public $cond;
    public $loop;
    public $stmts;
    public function __construct(array $subNodes = [], array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->init = $subNodes['init'] ?? [];
        $this->cond = $subNodes['cond'] ?? [];
        $this->loop = $subNodes['loop'] ?? [];
        $this->stmts = $subNodes['stmts'] ?? [];
    }
    public function getSubNodeNames() : array
    {
        return ['init', 'cond', 'loop', 'stmts'];
    }
    public function getType() : string
    {
        return 'Stmt_For';
    }
}
