<?php

declare(strict_types=1);

namespace newSrc\Mutagenesis;

use Closure;
use Infection\Mutation\Mutation;
use newSrc\Mutator\MutatorRegistry;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use SplObjectStorage;

/**
 * @phpstan-type MutationFactory = Closure(Node $node): iterable<\newSrc\Mutagenesis\Mutation>
 */
final class MutagenesisVisitor extends NodeVisitorAbstract
{
    /**
     * @var SplObjectStorage<Node, MutationFactory>
     */
    private readonly SplObjectStorage $potentialMutations;

    public function __construct(
        private readonly MutatorRegistry $mutatorRegistry,
    ) {
        $this->potentialMutations = new SplObjectStorage();
    }

    public function enterNode(Node $node): null
    {
        $this->potentialMutations->attach(
            $node,
            $this->mutatorRegistry->mutate(...),
        );

        return null;
    }

    /**
     * @return SplObjectStorage<Node, MutationFactory>
     */
    public function getPotentialMutations(): SplObjectStorage
    {
        return $this->potentialMutations;
    }
}
