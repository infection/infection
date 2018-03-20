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

class Minus extends Mutator
{
    /**
     * Replaces "-" with "+"
     *
     * @param Node $node
     *
     * @return Node\Expr\BinaryOp\Plus
     */
    public function mutate(Node $node)
    {
        return new Node\Expr\BinaryOp\Plus($node->left, $node->right, $node->getAttributes());
    }

    public function shouldMutate(Node $node): bool
    {
        return $node instanceof Node\Expr\BinaryOp\Minus;
    }
}
