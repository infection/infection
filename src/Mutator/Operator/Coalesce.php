<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Operator;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;

/**
 * @internal
 */
final class Coalesce extends Mutator
{
    /**
     * Replaces "'someValue' ?? 'otherValue';" with "'otherValue'"
     *
     * @param Node $node
     *
     * @return iterable
     */
    public function mutate(Node $node): iterable
    {
        yield $node->right;
    }

    protected function mutatesNode(Node $node): bool
    {
        return $node instanceof Node\Expr\BinaryOp\Coalesce;
    }
}
