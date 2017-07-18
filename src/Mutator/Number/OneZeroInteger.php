<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types = 1);

namespace Infection\Mutator\Number;

use Infection\Mutator\Mutator;
use PhpParser\Node;

class OneZeroInteger implements Mutator
{
    /**
     * Replaces "0" with "1" or "1" with "0"
     *
     * @param Node $node
     * @return Node\Scalar\LNumber
     */
    public function mutate(Node $node)
    {
        if ($node->value === 0) {
            return new Node\Scalar\LNumber(1);
        }

        return new Node\Scalar\LNumber(0);
    }

    public function shouldMutate(Node $node): bool
    {
        return $node instanceof Node\Scalar\LNumber && ($node->value === 0 || $node->value === 1);
    }
}