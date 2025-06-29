<?php

namespace newSrc\Mutagenesis\Strategy;

use newSrc\Mutagenesis\Mutation;
use newSrc\Mutation\NodeVisitor\MutationCollectorVisitor;
use PhpParser\Node;
use SplObjectStorage;

/**
 * @phpstan-import-type MutationFactory from MutationCollectorVisitor
 */
interface Strategy
{

    /**
     * @param SplObjectStorage<Node, MutationFactory> $potentialMutations
     *
     * @return iterable<Mutation>
     */
    public function apply(SplObjectStorage $potentialMutations): iterable;
}
