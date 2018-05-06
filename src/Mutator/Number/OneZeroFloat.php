<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Number;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;

/**
 * @internal
 */
final class OneZeroFloat extends Mutator
{
    /**
     * Replaces "0.0" with "1.0" or "1.0" with "0.0"
     *
     * @param Node $node
     *
     * @return Node\Scalar\DNumber
     */
    public function mutate(Node $node)
    {
        if ($node->value == 0.0) {
            return new Node\Scalar\DNumber(1.0);
        }

        return new Node\Scalar\DNumber(0.0);
    }

    protected function mutatesNode(Node $node): bool
    {
        return $node instanceof Node\Scalar\DNumber && ($node->value == 0.0 || $node->value == 1.0);
    }
}
