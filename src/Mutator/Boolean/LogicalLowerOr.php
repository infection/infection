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
final class LogicalLowerOr extends Mutator
{
    /**
     * Replaces "or" with "and"
     *
     * @param Node $node
     *
     * @return Node\Expr\BinaryOp\LogicalAnd
     */
    public function mutate(Node $node)
    {
        return new Node\Expr\BinaryOp\LogicalAnd($node->left, $node->right, $node->getAttributes());
    }

    protected function mutatesNode(Node $node): bool
    {
        return $node instanceof Node\Expr\BinaryOp\LogicalOr;
    }
}
