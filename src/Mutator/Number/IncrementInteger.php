<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Number;

use Infection\Mutator\Mutator;
use PhpParser\Node;

class IncrementInteger extends Mutator
{
    /**
     * Increments an integer by 1.
     *
     * @param Node $node
     *
     * @return Node\Scalar\LNumber
     */
    public function mutate(Node $node)
    {
        return new Node\Scalar\LNumber($node->value + 1);
    }

    public function shouldMutate(Node $node): bool
    {
        return $node instanceof Node\Scalar\LNumber && $node->value !== 0;
    }
}
