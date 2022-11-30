<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Node\Expr\BinaryOp;

use _HumbugBoxb47773b41c19\PhpParser\Node\Expr\BinaryOp;
class NotIdentical extends BinaryOp
{
    public function getOperatorSigil() : string
    {
        return '!==';
    }
    public function getType() : string
    {
        return 'Expr_BinaryOp_NotIdentical';
    }
}
