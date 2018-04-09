<?php

declare(strict_types=1);

namespace Infection\Mutator\Cast;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;

abstract class AbstractCastMutator extends Mutator
{
    public function mutate(Node $node)
    {
        return $node->expr;
    }
}
