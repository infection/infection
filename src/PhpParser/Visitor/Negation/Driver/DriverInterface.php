<?php

declare(strict_types=1);

namespace Infection\PhpParser\Visitor\Negation\Driver;

use PhpParser\Node;

interface DriverInterface
{
    public function instanceOf(Node\Expr $expr): bool;

    public function create(Node\Expr $left, Node\Expr $right, array $attributes): Node\Expr;
}
