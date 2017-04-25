<?php

declare(strict_types=1);

namespace Infection\Mutator\Number;

use Infection\Mutator\Mutator;
use PhpParser\Node;

class OneZeroInteger implements Mutator
{
    public function mutate(Node $node)
    {
        if ($node->value === 0) {
            return new Node\Scalar\LNumber(1);
        }

        return new Node\Scalar\LNumber(0);
    }

    public function shouldMutate(Node $node): bool
    {
        return $node instanceof Node\Scalar\LNumber && ($node->value === 0 || $node->value === 1);
    }
}