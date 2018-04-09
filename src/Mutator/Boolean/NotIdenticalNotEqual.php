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

final class NotIdenticalNotEqual extends Mutator
{
    /**
     * Replaces "!==" with "!="
     *
     * @param Node $node
     *
     * @return Node\Expr\BinaryOp\Equal
     */
    public function mutate(Node $node)
    {
        return new Node\Expr\BinaryOp\NotEqual($node->left, $node->right, $node->getAttributes());
    }

    protected function mutatesNode(Node $node): bool
    {
        return $node instanceof Node\Expr\BinaryOp\NotIdentical;
    }
}
