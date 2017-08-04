<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Mutator\Number;

use Infection\Mutator\FunctionBodyMutator;
use PhpParser\Node;

class OneZeroFloat extends FunctionBodyMutator
{
    public function mutate(Node $node)
    {
        if ($node->value === 0.0) {
            return new Node\Scalar\DNumber(1.0);
        }

        return new Node\Scalar\DNumber(0.0);
    }

    public function shouldMutate(Node $node): bool
    {
        return $node instanceof Node\Scalar\DNumber && ($node->value === 0.0 || $node->value === 1.0);
    }
}
