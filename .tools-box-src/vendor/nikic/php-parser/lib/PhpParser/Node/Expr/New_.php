<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Node\Expr;

use _HumbugBoxb47773b41c19\PhpParser\Node;
use _HumbugBoxb47773b41c19\PhpParser\Node\Arg;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr;
use _HumbugBoxb47773b41c19\PhpParser\Node\VariadicPlaceholder;
class New_ extends CallLike
{
    public $class;
    public $args;
    public function __construct($class, array $args = [], array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->class = $class;
        $this->args = $args;
    }
    public function getSubNodeNames() : array
    {
        return ['class', 'args'];
    }
    public function getType() : string
    {
        return 'Expr_New';
    }
    public function getRawArgs() : array
    {
        return $this->args;
    }
}
