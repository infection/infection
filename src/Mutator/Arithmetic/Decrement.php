<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Arithmetic;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;

/**
 * @internal
 */
final class Decrement extends Mutator
{
    /**
     * Replaces "--" with "++"
     *
     * @param Node $node
     *
     * @return \Generator
     */
    public function mutate(Node $node): \Generator
    {
        if ($node instanceof PreDec) {
            yield new PreInc($node->var, $node->getAttributes());
        }

        if ($node instanceof PostDec) {
            yield new PostInc($node->var, $node->getAttributes());
        }
    }

    protected function mutatesNode(Node $node): bool
    {
        return $node instanceof PreDec || $node instanceof PostDec;
    }
}
