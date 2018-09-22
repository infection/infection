<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\ZeroIteration;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;

/**
 * @internal
 */
final class Foreach_ extends Mutator
{
    /**
     * Replaces "foreach($a as $b)" with "foreach(array() as $b)"
     *
     * @param Node $node
     *
     * @return Node\Stmt\Foreach_
     */
    public function mutate(Node $node)
    {
        return new Node\Stmt\Foreach_(
            new Node\Expr\Array_(),
            $node->valueVar,
            [
                'keyVar' => $node->keyVar,
                'byRef' => $node->byRef,
                'stmts' => $node->stmts,
            ],
            $node->getAttributes()
        );
    }

    protected function mutatesNode(Node $node): bool
    {
        return $node instanceof Node\Stmt\Foreach_;
    }
}
