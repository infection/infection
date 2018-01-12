<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Boolean;

use Infection\Mutator\FunctionBodyMutator;
use PhpParser\Node;

class TrueValue extends FunctionBodyMutator
{
    /**
     * Replaces "true" with "false"
     *
     * @param Node $node
     *
     * @return Node\Expr\ConstFetch
     */
    public function mutate(Node $node)
    {
        return new Node\Expr\ConstFetch(new Node\Name('false'));
    }

    public function shouldMutate(Node $node): bool
    {
        if (!($node instanceof Node\Expr\ConstFetch)) {
            return false;
        }

        return $node->name->getFirst() === 'true';
    }
}
