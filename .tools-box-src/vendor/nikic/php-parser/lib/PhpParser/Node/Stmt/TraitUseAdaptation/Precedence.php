<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\TraitUseAdaptation;

use _HumbugBoxb47773b41c19\PhpParser\Node;
class Precedence extends Node\Stmt\TraitUseAdaptation
{
    public $insteadof;
    public function __construct(Node\Name $trait, $method, array $insteadof, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->trait = $trait;
        $this->method = \is_string($method) ? new Node\Identifier($method) : $method;
        $this->insteadof = $insteadof;
    }
    public function getSubNodeNames() : array
    {
        return ['trait', 'method', 'insteadof'];
    }
    public function getType() : string
    {
        return 'Stmt_TraitUseAdaptation_Precedence';
    }
}
