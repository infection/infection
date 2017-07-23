<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Mutator\Arithmetic;

use Infection\Mutator\Mutator;
use PhpParser\Node;

class MinusEqual implements Mutator
{
    /**
     * Replaces "-=" with "+="
     *
     * @param Node $node
     *
     * @return Node\Expr\AssignOp\Plus
     */
    public function mutate(Node $node)
    {
        return new Node\Expr\AssignOp\Plus($node->var, $node->expr, $node->getAttributes());
    }

    public function shouldMutate(Node $node): bool
    {
        return $node instanceof Node\Expr\AssignOp\Minus;
    }
}
