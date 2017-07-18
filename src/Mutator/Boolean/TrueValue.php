<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types = 1);


namespace Infection\Mutator\Boolean;


use Infection\Mutator\Mutator;
use PhpParser\Node;

class TrueValue implements Mutator
{
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