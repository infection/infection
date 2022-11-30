<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\Node\Scalar;

use _HumbugBox9658796bb9f0\PhpParser\Node\Expr;
use _HumbugBox9658796bb9f0\PhpParser\Node\Scalar;
class Encapsed extends Scalar
{
    public $parts;
    public function __construct(array $parts, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->parts = $parts;
    }
    public function getSubNodeNames() : array
    {
        return ['parts'];
    }
    public function getType() : string
    {
        return 'Scalar_Encapsed';
    }
}
