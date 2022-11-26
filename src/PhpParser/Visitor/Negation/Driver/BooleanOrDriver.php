<?php

declare(strict_types=1);

namespace Infection\PhpParser\Visitor\Negation\Driver;

use PhpParser\Node;

final class BooleanOrDriver implements DriverInterface
{
    public function instanceOf(Node\Expr $expr): bool
    {
        return $expr instanceof Node\Expr\BinaryOp\BooleanOr;
    }

    public function create(Node\Expr $left, Node\Expr $right, array $attributes): Node\Expr
    {
        return new Node\Expr\BinaryOp\BooleanOr($left, $right, $attributes);
    }
}
