<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);


namespace Infection\Mutator\Arithmetic;


use Infection\Mutator\Mutator;
use PhpParser\Node;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PreDec;

class Increment implements Mutator
{
    /**
     * Replaces "++" with "--"
     *
     * @param Node $node
     * @return PostDec|PreDec
     */
    public function mutate(Node $node)
    {
        if ($node instanceof PreInc) {
            return new PreDec($node->var, $node->getAttributes());
        }

        if ($node instanceof PostInc) {
            return new Node\Expr\PostDec($node->var, $node->getAttributes());
        }
    }

    public function shouldMutate(Node $node): bool
    {
        return $node instanceof PreInc || $node instanceof PostInc;
    }
}