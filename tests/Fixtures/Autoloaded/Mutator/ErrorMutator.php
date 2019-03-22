<?php

namespace Infection\WrongMutator;


use Infection\Mutator\Util\BaseMutator;
use PhpParser\Node;

class ErrorMutator extends BaseMutator
{
    public function mutate(Node $node)
    {
        return $node;
    }

    protected function mutatesNode(Node $node): bool
    {
        //This is intended to cause an error.
        $name = (string) $node;

        return $name == 'foo';
    }
}
