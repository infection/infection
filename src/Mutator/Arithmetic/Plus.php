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
use PhpParser\Node\Expr\Array_;

/**
 * @internal
 */
final class Plus extends Mutator
{
    /**
     * Replaces "+" with "-"
     *
     * @param Node $node
     *
     * @return iterable
     */
    public function mutate(Node $node): iterable
    {
        yield new Node\Expr\BinaryOp\Minus($node->left, $node->right, $node->getAttributes());
    }

    protected function mutatesNode(Node $node): bool
    {
        if (!($node instanceof Node\Expr\BinaryOp\Plus)) {
            return false;
        }

        if ($node->left instanceof Array_ || $node->right instanceof Array_) {
            return false;
        }

        return true;
    }
}
