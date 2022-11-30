<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Node\Expr;

use _HumbugBoxb47773b41c19\PhpParser\Node\Expr;
class ClosureUse extends Expr
{
    public $var;
    public $byRef;
    public function __construct(Expr\Variable $var, bool $byRef = \false, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->var = $var;
        $this->byRef = $byRef;
    }
    public function getSubNodeNames() : array
    {
        return ['var', 'byRef'];
    }
    public function getType() : string
    {
        return 'Expr_ClosureUse';
    }
}
