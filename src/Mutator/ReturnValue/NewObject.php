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
final class NewObject extends AbstractValueToNullReturnValue
{
    /**
     * Replaces "return new Something(anything);" with "new Something(anything); return null;"
     *
     * @param Node $node
     *
     * @return iterable
     */
    public function mutate(Node $node): iterable
    {
        yield [
            new Node\Stmt\Expression($node->expr),
            new Node\Stmt\Return_(
                new Node\Expr\ConstFetch(new Node\Name('null'))
            ),
        ];
    }

    protected function mutatesNode(Node $node): bool
    {
        if (!$node instanceof Node\Stmt\Return_) {
            return false;
        }

        if (!$node->expr instanceof Node\Expr\New_) {
            return false;
        }

        if (!$node->expr->class instanceof Node\Name) {
            return false;
        }

        return $this->isNullReturnValueAllowed($node);
    }
}
