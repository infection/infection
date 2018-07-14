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
final class NumberToString extends Mutator
{
    /**
     * Casts a number to string.
     *
     * @param Node $node
     *
     * @return Node\Scalar\String_
     */
    public function mutate(Node $node)
    {
        return new Node\Scalar\String_((string) $node->value);
    }

    protected function mutatesNode(Node $node): bool
    {
        return $node instanceof Node\Scalar\DNumber || $node instanceof Node\Scalar\LNumber;
    }
}
