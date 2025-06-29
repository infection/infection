<?php

declare(strict_types=1);

namespace newSrc\Mutator;

use newSrc\Mutagenesis\Mutation;
use newSrc\Mutator\FirstOrder\ConditionalNegotiation\Equal;
use PhpParser\Node;

final class MutatorRegistry
{
    /**
     * @param Mutator[] $mutators
     */
    public function __construct(
        public readonly array $mutators = [],   // TODO: inject
    ) {
        $this->mutators = [
            new Equal(),
        ];
    }

    /**
     * @return iterable<Mutation>
     */
    public function mutate(Node $node): iterable
    {
        foreach ($this->mutators as $mutator) {
            yield $mutator->mutate($node);
        }
    }
}
