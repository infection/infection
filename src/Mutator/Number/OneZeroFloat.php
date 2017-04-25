<?php

declare(strict_types=1);

namespace Infection\Mutator\Number;

use Infection\Mutator\Mutator;
use PhpParser\Node;

class OneZeroFloat implements Mutator
{
    public function mutate(Node $node)
    {
        if ($node->value === 0.0) {
            return new Node\Scalar\LNumber(1.0);
        }

        return new Node\Scalar\LNumber(0.0);
    }

    public function shouldMutate(Node $node): bool
    {
        return $node instanceof Node\Scalar\LNumber && ($node->value === 0.0 || $node->value === 1.0);
    }
}