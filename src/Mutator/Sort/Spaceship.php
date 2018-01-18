<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Sort;

use Infection\Mutator\Mutator;
use PhpParser\Node;

class Spaceship extends Mutator
{
    /**
     * Swaps the arguments in the Spaceship operator <=>
     *
     * @param Node $node
     *
     * @return Node\Expr\BinaryOp\Spaceship
     */
    public function mutate(Node $node)
    {
        return new Node\Expr\BinaryOp\Spaceship($node->right, $node->left);
    }

    public function shouldMutate(Node $node): bool
    {
        return $node instanceof Node\Expr\BinaryOp\Spaceship;
    }
}
