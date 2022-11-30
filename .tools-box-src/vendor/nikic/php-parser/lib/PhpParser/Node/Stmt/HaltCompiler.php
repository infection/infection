<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Node\Stmt;

use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt;
class HaltCompiler extends Stmt
{
    public $remaining;
    public function __construct(string $remaining, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->remaining = $remaining;
    }
    public function getSubNodeNames() : array
    {
        return ['remaining'];
    }
    public function getType() : string
    {
        return 'Stmt_HaltCompiler';
    }
}
