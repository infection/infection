<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Operator;

use Infection\Mutator\Util\Mutator;
use Infection\Visitor\ParentConnectorVisitor;
use PhpParser\Node;

/**
 * @internal
 */
final class Coalesce extends Mutator
{
    /**
     * Replaces "'someValue' ?? 'otherValue';" with "(unset) 'someValue' ?? 'otherValue'"
     *
     * @param Node $node
     *
     * @return Node\Expr\BinaryOp\Coalesce
     */
    public function mutate(Node $node)
    {
        return new Node\Expr\BinaryOp\Coalesce(new Node\Expr\Cast\Unset_($node->left), $node->right);
    }

    protected function mutatesNode(Node $node): bool
    {
        return $node instanceof Node\Expr\BinaryOp\Coalesce;
    }
}