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
final class FalseValue extends Mutator
{
    /**
     * Replaces "false" with "true"
     *
     * @param Node $node
     *
     * @return Node\Expr\ConstFetch
     */
    public function mutate(Node $node)
    {
        return new Node\Expr\ConstFetch(new Node\Name('true'));
    }

    protected function mutatesNode(Node $node): bool
    {
        if (!($node instanceof Node\Expr\ConstFetch)) {
            return false;
        }

        return $node->name->toLowerString() === 'false';
    }
}
