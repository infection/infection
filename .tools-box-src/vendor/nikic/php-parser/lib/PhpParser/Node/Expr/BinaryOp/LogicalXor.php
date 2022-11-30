<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Node\Expr\BinaryOp;

use _HumbugBoxb47773b41c19\PhpParser\Node\Expr\BinaryOp;
class LogicalXor extends BinaryOp
{
    public function getOperatorSigil() : string
    {
        return 'xor';
    }
    public function getType() : string
    {
        return 'Expr_BinaryOp_LogicalXor';
    }
}
