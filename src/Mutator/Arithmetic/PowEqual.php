<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Arithmetic;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;

/**
 * @internal
 */
final class PowEqual extends Mutator
{
    /**
     * Replaces "**=" with "/="
     *
     * @param Node $node
     *
     * @return iterable
     */
    public function mutate(Node $node): iterable
    {
        yield new Node\Expr\AssignOp\Div($node->var, $node->expr, $node->getAttributes());
    }

    protected function mutatesNode(Node $node): bool
    {
        return $node instanceof Node\Expr\AssignOp\Pow;
    }
}
