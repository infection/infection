<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Boolean;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;

/**
 * @internal
 */
final class LogicalNot extends Mutator
{
    /**
     * Replaces "!something" with "something"
     *
     * @param Node $node
     *
     * @return iterable
     */
    public function mutate(Node $node): iterable
    {
        yield $node->expr;
    }

    protected function mutatesNode(Node $node): bool
    {
        if (!($node instanceof Node\Expr\BooleanNot)) {
            return false;
        }

        // e.g. "!!someFunc()"
        $isDoubledLogicalNot = ($node->expr instanceof Node\Expr\BooleanNot) ||
            $node->getAttribute('parent') instanceof Node\Expr\BooleanNot;

        return !$isDoubledLogicalNot;
    }
}
