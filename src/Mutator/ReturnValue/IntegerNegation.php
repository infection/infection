<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\ReturnValue;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;

/**
 * @internal
 */
final class IntegerNegation extends Mutator
{
    /**
     * Replaces any integer with negated integer value.
     * Replaces "-5" with "5"
     *
     * @param Node $node
     *
     * @return Node\Stmt\Return_
     */
    public function mutate(Node $node)
    {
        $integerValue = $node->expr instanceof Node\Expr\UnaryMinus
            ? -$node->expr->expr->value
            : $node->expr->value;

        return new Node\Stmt\Return_(
            new Node\Scalar\LNumber(-1 * $integerValue, $node->getAttributes())
        );
    }

    protected function mutatesNode(Node $node): bool
    {
        if (!$node instanceof Node\Stmt\Return_) {
            return false;
        }

        $expr = $node->expr;

        if ($expr instanceof Node\Expr\UnaryMinus) {
            $expr = $node->expr->expr;
        }

        if (!$expr instanceof Node\Scalar\LNumber) {
            return false;
        }

        if ($expr->value == 0) {
            return false;
        }

        return true;
    }
}
