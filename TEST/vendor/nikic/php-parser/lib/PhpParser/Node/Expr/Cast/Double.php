<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\Node\Expr\Cast;

use _HumbugBox9658796bb9f0\PhpParser\Node\Expr\Cast;
class Double extends Cast
{
    const KIND_DOUBLE = 1;
    const KIND_FLOAT = 2;
    const KIND_REAL = 3;
    public function getType() : string
    {
        return 'Expr_Cast_Double';
    }
}
