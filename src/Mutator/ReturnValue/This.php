<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\ReturnValue;

use Infection\Mutator\Util\AbstractValueToNullReturnValue;
use PhpParser\Node;

/**
 * @internal
 */
final class This extends AbstractValueToNullReturnValue
{
    /**
     * Replaces "return $this;" with "return null;"
     *
     * @param Node $node
     *
     * @return Node\Stmt\Return_
     */
    public function mutate(Node $node)
    {
        return new Node\Stmt\Return_(
            new Node\Expr\ConstFetch(new Node\Name('null'))
        );
    }

    protected function mutatesNode(Node $node): bool
    {
        return $node instanceof Node\Stmt\Return_ &&
            $node->expr instanceof Node\Expr\Variable &&
            $node->expr->name == 'this'
            && $this->isNullReturnValueAllowed($node);
    }
}
