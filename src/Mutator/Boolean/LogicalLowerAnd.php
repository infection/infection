<?php

declare(strict_types=1);


namespace Infection\Mutator\Boolean;


use Infection\Mutator\Mutator;
use PhpParser\Node;

class LogicalLowerAnd implements Mutator
{
    /**
     * Replcaes "and" with "or"
     *
     * @param Node $node
     * @return Node\Expr\BinaryOp\LogicalOr
     */
    public function mutate(Node $node)
    {
        return new Node\Expr\BinaryOp\LogicalOr($node->left, $node->right, $node->getAttributes());
    }

    public function shouldMutate(Node $node): bool
    {
        return $node instanceof Node\Expr\BinaryOp\LogicalAnd;
    }
}