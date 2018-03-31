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

final class ArrayItem extends Mutator
{
    /**
     * Replaces [$a->foo => $b->bar] with [$a->foo > $b->bar]
     *
     * @param Node $node
     *
     * @return Node\Expr\BinaryOp\Greater
     */
    public function mutate(Node $node)
    {
        return new Node\Expr\BinaryOp\Greater($node->key, $node->value, $node->getAttributes());
    }

    protected function mutatesNode(Node $node): bool
    {
        return $node instanceof Node\Expr\ArrayItem && $node->key && ($this->isNodeWithSideEffects($node->value) || $this->isNodeWithSideEffects($node->key));
    }

    private function isNodeWithSideEffects(Node $node)
    {
        return
            // __get() can have side-effects
            $node instanceof Node\Expr\PropertyFetch ||
            // these clearly can have side-effects
            $node instanceof Node\Expr\MethodCall ||
            $node instanceof Node\Expr\FuncCall;
    }
}
