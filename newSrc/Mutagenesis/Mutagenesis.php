<?php

declare(strict_types=1);

namespace newSrc\Mutagenesis;

// Based on the current MutationGenerator
use newSrc\Mutagenesis\Strategy\Strategy;
use newSrc\Mutator\MutatorRegistry;
use PhpParser\Node;
use PhpParser\NodeTraverser;

final class Mutagenesis
{
    public function __construct(
        private MutatorRegistry $mutatorRegistry,
        private Strategy $strategy,
    ) {
    }

    /**
     * @param Node[] $nodes
     *
     * @return iterable<Mutation>
     */
    public function generate(array $nodes): iterable
    {
        $visitor = new MutagenesisVisitor($this->mutatorRegistry);

        $traverser = new NodeTraverser($visitor);
        $traverser->traverse($nodes);

        // TODO: the logic here is likely gonna be complex and needs experimenting.
        // The idea is that:
        // - mutations are generated, one by one
        // - depending on the strategy employed, _more_ may be requested, but maybe not.
        // - the ones yielded by the strategy applied are evaluated.
        // - this means:
        //      - We cannot know ahead of time the number of mutations issued.
        //      - we do not generate all mutations at once
        // Note certain that the design is good enough: need to check how can a strategy know if it needs
        // to yield more. Maybe need to be injected a service that can be mutated downstream.
        yield from $this->strategy->apply($visitor->getPotentialMutations());
    }
}