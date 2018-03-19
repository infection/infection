<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Arithmetic;

use Infection\Mutator\Mutator;
use PhpParser\Node;

class Assignment extends Mutator
{
    /**
     * Replaces "+=", "*=", ".=", and similar with a plain "="
     *
     * @param Node $node
     *
     * @return Node\Expr\Assign
     */
    public function mutate(Node $node)
    {
        return new Node\Expr\Assign($node->var, $node->expr, $node->getAttributes());
    }

    public function shouldMutate(Node $node): bool
    {
        return $node instanceof Node\Expr\AssignOp;
    }
}
