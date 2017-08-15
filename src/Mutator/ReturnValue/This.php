<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Mutator\ReturnValue;

use Infection\Mutator\FunctionBodyMutator;
use PhpParser\Node;

class This extends AbstractValueToNullReturnValue
{
    /**
     * Replaces "return $this;" with "return null;"
     *
     * @param Node $node
     * @return Node\Stmt\Return_
     */
    public function mutate(Node $node)
    {
        return new Node\Stmt\Return_(
            new Node\Expr\ConstFetch(new Node\Name('null'))
        );
    }

    public function shouldMutate(Node $node): bool
    {
        return $node instanceof Node\Stmt\Return_ &&
            $node->expr instanceof Node\Expr\Variable &&
            $node->expr->name === 'this'
            && $this->isNullReturnValueAllowed($node);
    }
}
