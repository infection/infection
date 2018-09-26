<?php

namespace Infection\WrongMutator;

use Infection\Mutator;
use Infection\Mutator\Util\Replacer\NodeReplacement;
use PhpParser\Node;

class ErrorMutator extends Mutator
{
    public function mutate(Node $node): \Generator
    {
        yield [];
    }

    protected function mutatesNode(Node $node): bool
    {
        //This is intended to cause an error.
        $name = (string) $node;

        return $name == 'foo';
    }
}
