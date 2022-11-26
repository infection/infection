<?php

declare(strict_types=1);

namespace Infection\PhpParser\Visitor\Negation\Driver;

use PhpParser\Node;

final class BooleanAndDriver implements DriverInterface
{
    public function instanceOf(Node\Expr $expr): bool
    {
        return $expr instanceof Node\Expr\BinaryOp\BooleanAnd;
    }

    public function create(Node\Expr $left, Node\Expr $right, array $attributes): Node\Expr
    {
        return new Node\Expr\BinaryOp\BooleanAnd($left, $right, $attributes);
    }
}
