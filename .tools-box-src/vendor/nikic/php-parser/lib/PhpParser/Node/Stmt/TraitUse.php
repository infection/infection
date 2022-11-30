<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Node\Stmt;

use _HumbugBoxb47773b41c19\PhpParser\Node;
class TraitUse extends Node\Stmt
{
    public $traits;
    public $adaptations;
    public function __construct(array $traits, array $adaptations = [], array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->traits = $traits;
        $this->adaptations = $adaptations;
    }
    public function getSubNodeNames() : array
    {
        return ['traits', 'adaptations'];
    }
    public function getType() : string
    {
        return 'Stmt_TraitUse';
    }
}
