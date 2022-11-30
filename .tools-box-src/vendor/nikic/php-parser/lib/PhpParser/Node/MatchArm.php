<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Node;

use _HumbugBoxb47773b41c19\PhpParser\Node;
use _HumbugBoxb47773b41c19\PhpParser\NodeAbstract;
class MatchArm extends NodeAbstract
{
    public $conds;
    public $body;
    public function __construct($conds, Node\Expr $body, array $attributes = [])
    {
        $this->conds = $conds;
        $this->body = $body;
        $this->attributes = $attributes;
    }
    public function getSubNodeNames() : array
    {
        return ['conds', 'body'];
    }
    public function getType() : string
    {
        return 'MatchArm';
    }
}
