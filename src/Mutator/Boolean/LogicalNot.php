<?php

declare(strict_types=1);

namespace Infection\Mutator\Boolean;


use Infection\Mutator\Mutator;
use PhpParser\Node;

class LogicalNot implements Mutator
{
    public function mutate(Node $node)
    {
        return $node->expr;
    }

    public function shouldMutate(Node $node): bool
    {
        if (! ($node instanceof Node\Expr\BooleanNot)) {
            return false;
        }

        // e.g. "!!someFunc()"
        $isDoubledLogicalNot = ($node->expr instanceof Node\Expr\BooleanNot) ||
            $node->getAttribute('parent') instanceof Node\Expr\BooleanNot;

        return !$isDoubledLogicalNot;
    }
}