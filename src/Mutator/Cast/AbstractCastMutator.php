<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Cast;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;

/**
 * @internal
 */
abstract class AbstractCastMutator extends Mutator
{
    /**
     * Replaces "(cast) $foo;" with "$foo;"
     *
     * @param Node $node
     *
     * @return Node
     */
    public function mutate(Node $node)
    {
        yield $node->expr;
    }
}
