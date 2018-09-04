<?php

namespace Infection\WrongMutator;


use Infection\Mutator\Util\Mutator;
use PhpParser\Node;

class ErrorMutator extends Mutator
{
    public function mutate(Node $node): \Generator
    {
        yield $node;
    }

    protected function mutatesNode(Node $node): bool
    {
        //This is intended to cause an error.
        $name = (string) $node;

        return $name == 'foo';
    }
}
