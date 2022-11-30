<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Cast;

use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
abstract class AbstractCastMutator implements Mutator
{
    use GetMutatorName;
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        (yield $node->expr);
    }
}
