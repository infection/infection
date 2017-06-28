<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);


namespace Infection\Mutator\ReturnValue;


use Infection\Mutator\Mutator;
use PhpParser\Node;

class NewObject implements Mutator
{
    /**
     * Replace "return new Something(anything);" with "new Something(anything); return null;"
     * @param Node $node
     * @return array
     */
    public function mutate(Node $node)
    {
        return [
            $node->expr,
            new Node\Stmt\Return_(
                new Node\Expr\ConstFetch(new Node\Name('null'))
            )
        ];
    }

    public function shouldMutate(Node $node): bool
    {
        if (! $node instanceof Node\Stmt\Return_) {
            return false;
        }

        if (! $node->expr instanceof Node\Expr\New_) {
            return false;
        }

        if (! $node->expr->class instanceof Node\Name\FullyQualified) {
            return false;
        }

        return true;
    }
}