<?php

declare(strict_types=1);


namespace Infection\Mutator\ConditionalBoundary;


use Infection\Mutator\Mutator;
use PhpParser\Node;

class LessThanOrEqualTo implements Mutator
{
    public function mutate(Node $node)
    {
        return new Node\Expr\BinaryOp\Smaller($node->left, $node->right, $node->getAttributes());
    }

    public function shouldMutate(Node $node): bool
    {
        return $node instanceof Node\Expr\BinaryOp\SmallerOrEqual;
    }

}