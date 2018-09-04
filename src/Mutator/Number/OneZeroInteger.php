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
final class OneZeroInteger extends Mutator
{
    /**
     * Replaces "0" with "1" or "1" with "0"
     *
     * @param Node $node
     *
     * @return \Generator
     */
    public function mutate(Node $node): \Generator
    {
        if ($node->value === 0) {
            yield new Node\Scalar\LNumber(1);

            return;
        }

        yield new Node\Scalar\LNumber(0);
    }

    protected function mutatesNode(Node $node): bool
    {
        return $node instanceof Node\Scalar\LNumber && ($node->value === 0 || $node->value === 1);
    }
}
