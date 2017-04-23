<?php

declare(strict_types=1);

namespace Infection\Mutator\ConditionalNegotiation;


use Infection\Mutator\Mutator;
use PhpParser\Node;

class NotIdentical implements Mutator
{
    /**
     * Replaces "!==" with "==="
     *
     * @param Node $node
     * @return Node\Expr\BinaryOp\Identical
     */
    public function mutate(Node $node)
    {
        return new Node\Expr\BinaryOp\Identical($node->left, $node->right, $node->getAttributes());
    }

    public function shouldMutate(Node $node): bool
    {
        return $node instanceof Node\Expr\BinaryOp\NotIdentical;
    }

}