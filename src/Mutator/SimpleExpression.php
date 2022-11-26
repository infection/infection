<?php

declare(strict_types=1);

namespace Infection\Mutator;

use PhpParser\Node;

trait SimpleExpression
{
    private function isSimpleExpression(Node\Expr $expr): bool
    {
        return $expr instanceof Node\Expr\FuncCall
            || $expr instanceof Node\Expr\MethodCall
            || $expr instanceof Node\Expr\StaticCall
            || $expr instanceof Node\Expr\Variable
            || $expr instanceof Node\Expr\ArrayDimFetch
            || $expr instanceof Node\Expr\ClassConstFetch;
    }
}
