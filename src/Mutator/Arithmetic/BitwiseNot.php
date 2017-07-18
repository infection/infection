<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types = 1);


namespace Infection\Mutator\Arithmetic;


use Infection\Mutator\Mutator;
use PhpParser\Node;

class BitwiseNot implements Mutator
{
    /**
     * Replaces "~" with "" (removed)
     *
     * @param Node $node
     * @return mixed
     */
    public function mutate(Node $node)
    {
        return $node->expr;
    }

    public function shouldMutate(Node $node): bool
    {
        return $node instanceof Node\Expr\BitwiseNot;
    }
}