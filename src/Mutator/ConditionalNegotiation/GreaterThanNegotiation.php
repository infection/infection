<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\ConditionalNegotiation;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;

/**
 * @internal
 */
final class GreaterThanNegotiation extends Mutator
{
    /**
     * Replaces ">" with "<="
     *
     * @param Node $node
     *
     * @return Node\Expr\BinaryOp\SmallerOrEqual
     */
    public function mutate(Node $node)
    {
        yield new Node\Expr\BinaryOp\SmallerOrEqual($node->left, $node->right, $node->getAttributes());
    }

    protected function mutatesNode(Node $node): bool
    {
        return $node instanceof Node\Expr\BinaryOp\Greater;
    }
}
