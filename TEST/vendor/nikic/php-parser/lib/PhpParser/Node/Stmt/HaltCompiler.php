<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\Node\Stmt;

use _HumbugBox9658796bb9f0\PhpParser\Node\Stmt;
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
