<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Mutator\ReturnValue;

use Infection\Mutator\Mutator;
use PhpParser\Node;

class FloatNegation implements Mutator
{
    public function mutate(Node $node)
    {
        $floatValue = $node->expr instanceof Node\Expr\UnaryMinus
            ? -$node->expr->expr->value
            : $node->expr->value;

        return new Node\Stmt\Return_(
            new Node\Scalar\DNumber(-1 * $floatValue, $node->getAttributes())
        );
    }

    public function shouldMutate(Node $node): bool
    {
        if (!$node instanceof Node\Stmt\Return_) {
            return false;
        }

        $expr = $node->expr;

        if ($expr instanceof Node\Expr\UnaryMinus) {
            $expr = $node->expr->expr;
        }

        if (!$expr instanceof Node\Scalar\DNumber) {
            return false;
        }

        if ($expr->value === 0.0) {
            return false;
        }

        return true;
    }
}
