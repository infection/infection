<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\Node\Expr\BinaryOp;

use _HumbugBox9658796bb9f0\PhpParser\Node\Expr\BinaryOp;
class Greater extends BinaryOp
{
    public function getOperatorSigil() : string
    {
        return '>';
    }
    public function getType() : string
    {
        return 'Expr_BinaryOp_Greater';
    }
}
