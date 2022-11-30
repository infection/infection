<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Node\Expr;

use _HumbugBoxb47773b41c19\PhpParser\Node\Expr;
class Include_ extends Expr
{
    const TYPE_INCLUDE = 1;
    const TYPE_INCLUDE_ONCE = 2;
    const TYPE_REQUIRE = 3;
    const TYPE_REQUIRE_ONCE = 4;
    public $expr;
    public $type;
    public function __construct(Expr $expr, int $type, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->expr = $expr;
        $this->type = $type;
    }
    public function getSubNodeNames() : array
    {
        return ['expr', 'type'];
    }
    public function getType() : string
    {
        return 'Expr_Include';
    }
}
