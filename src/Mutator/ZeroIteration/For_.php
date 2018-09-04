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
final class For_ extends Mutator
{
    /**
     * Replaces "for($i=0; $i<10; $i++)" with "for($i=0; false; $i++)"
     *
     * @param Node $node
     *
     * @return iterable
     */
    public function mutate(Node $node): iterable
    {
        yield new Node\Stmt\For_(
            [
                'init' => $node->init,
                'cond' => [new Node\Expr\ConstFetch(new Node\Name('false'))],
                'loop' => $node->loop,
                'stmts' => $node->stmts,
            ],
            $node->getAttributes()
        );
    }

    protected function mutatesNode(Node $node): bool
    {
        return $node instanceof Node\Stmt\For_;
    }
}
