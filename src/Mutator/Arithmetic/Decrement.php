<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Arithmetic;

use Infection\Mutator\FunctionBodyMutator;
use PhpParser\Node;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PreDec;

class Decrement extends FunctionBodyMutator
{
    /**
     * Replaces "--" with "++"
     *
     * @param Node $node
     *
     * @return PreInc|PostInc
     */
    public function mutate(Node $node)
    {
        if ($node instanceof PreDec) {
            return new PreInc($node->var, $node->getAttributes());
        }

        if ($node instanceof PostDec) {
            return new PostInc($node->var, $node->getAttributes());
        }
    }

    public function shouldMutate(Node $node): bool
    {
        return $node instanceof PreDec || $node instanceof PostDec;
    }
}
