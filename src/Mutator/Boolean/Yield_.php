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

/**
 * @internal
 */
final class Yield_ extends Mutator
{
    /**
     * Replaces "yield $a => $b;" with "yield $a > $b;"
     *
     * @param Node $node
     *
     * @return \Generator
     */
    public function mutate(Node $node): \Generator
    {
        $node->value = new Node\Expr\BinaryOp\Greater($node->key, $node->value, $node->getAttributes());
        $node->key = null;

        yield $node;
    }

    protected function mutatesNode(Node $node): bool
    {
        return $node instanceof Node\Expr\Yield_ && $node->key;
    }
}
