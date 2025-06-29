<?php

namespace newSrc\Mutator;

// AKA MutationOperator
use newSrc\Mutagenesis\Mutation;
use PhpParser\Node;

interface Mutator
{
    /**
     * @return iterable<Mutation>
     */
    public function mutate(Node $node): iterable;
}
