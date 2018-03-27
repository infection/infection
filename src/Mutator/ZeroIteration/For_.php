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

class For_ extends Mutator
{
    public function mutate(Node $node)
    {
        return new Node\Stmt\For_(
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
