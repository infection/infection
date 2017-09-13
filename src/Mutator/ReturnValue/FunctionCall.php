<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Mutator\ReturnValue;

use PhpParser\Node;

class FunctionCall extends AbstractValueToNullReturnValue
{
    /**
     * Replaces "return func();" with "func(); return null;"
     *
     * @param Node $node
     *
     * @return array
     */
    public function mutate(Node $node)
    {
        return [
            $node->expr,
            new Node\Stmt\Return_(
                new Node\Expr\ConstFetch(new Node\Name('null'))
            ),
        ];
    }

    public function shouldMutate(Node $node): bool
    {
        if (!$node instanceof Node\Stmt\Return_) {
            return false;
        }

        if (!$node->expr instanceof Node\Expr\FuncCall) {
            return false;
        }

        return $this->isNullReturnValueAllowed($node);
    }
}
