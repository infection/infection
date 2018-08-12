<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Rounding;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;

/**
 * @internal
 */
final class Round extends Mutator
{
    /**
     * Replaces "floor();" and "ceil();" with "round();"
     *
     * @param Node\Expr\FuncCall $node
     *
     * @return Node\Expr\FuncCall
     */
    public function mutate(Node $node)
    {
        return new Node\Expr\FuncCall(
            new Node\Name('round'),
            $node->args,
            $node->getAttributes()
        );
    }

    protected function mutatesNode(Node $node): bool
    {
        return $node instanceof Node\Expr\FuncCall && (
            strtolower((string) $node->name) === 'floor' ||
            strtolower((string) $node->name) === 'ceil'
        );
    }
}
