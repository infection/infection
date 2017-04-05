<?php

declare(strict_types=1);

namespace Infection\Mutator\Arithmetic;

use Infection\Mutator\Mutator;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;

class Plus implements Mutator
{
    public function mutate(Node $node)
    {
        return new Node\Expr\BinaryOp\Minus($node->left, $node->right, $node->getAttributes());
    }

    public function shouldMutate(Node $node) : bool
    {
        if (!($node instanceof Node\Expr\BinaryOp\Plus)) {
            return false;
        }

        if ($node->left instanceof Array_ && $node->right instanceof Array_) {
            return false;
        }

        return true;
    }
}
